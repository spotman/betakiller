<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Exception;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\Daemon\Runner;
use Psr\Log\LoggerInterface;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

class SupervisorDaemon implements DaemonInterface
{
    public const CODENAME = 'Supervisor';

    public const RETRY_LIMIT    = 60;
    public const RELOAD_SIGNAL  = \SIGUSR1;
    public const RESTART_SIGNAL = \SIGUSR2;

    private const STATUS_STARTING = 'starting';
    private const STATUS_RUNNING  = 'running';
    private const STATUS_FINISHED = 'finished';
    private const STATUS_STOPPING = 'stopping';
    private const STATUS_STOPPED  = 'stopped';
    private const STATUS_FAILED   = 'failed';

    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private ConfigProviderInterface $config;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * @var LoopInterface
     */
    private LoopInterface $loop;

    /**
     * @var int[]
     */
    private array $failureCounters = [];

    /**
     * @var mixed[][]
     */
    private array $statuses = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var \BetaKiller\Daemon\LockFactory
     */
    private LockFactory $lockFactory;

    private bool $isRunning = false;

    private bool $isStoppingDaemons = false;

    /**
     * Supervisor constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface $config
     * @param \BetaKiller\Helper\AppEnvInterface         $appEnv
     * @param \BetaKiller\Daemon\LockFactory             $lockFactory
     * @param \Psr\Log\LoggerInterface                   $logger
     */
    public function __construct(
        ConfigProviderInterface $config,
        AppEnvInterface $appEnv,
        LockFactory $lockFactory,
        LoggerInterface $logger
    ) {
        $this->config      = $config;
        $this->appEnv      = $appEnv;
        $this->logger      = $logger;
        $this->lockFactory = $lockFactory;
    }

    public function startDaemon(LoopInterface $loop): void
    {
        $this->loop = $loop;

        // Reload signal => hot restart
        $loop->addSignal(self::RELOAD_SIGNAL, function () {
            $this->logger->debug('Reloading stopped daemons');
            $this->restartStopped();
        });

        // Restart signal => hot restart
        $loop->addSignal(self::RESTART_SIGNAL, function () {
            $this->logger->debug('Restarting all daemons');
            $this->stopAll();
        });

        $this->addSupervisorTimer();

        foreach ($this->getDefinedDaemons() as $codename) {
            $this->startSupervisedDaemon($codename, true);
        }

        $this->isRunning = true;
    }

    private function addSupervisorTimer(): void
    {
        // Watch status changes
        $this->loop->addPeriodicTimer(0.1, function () {
            // Prevent auto-restart
            if (!$this->isRunning || $this->isStoppingDaemons) {
                return;
            }

            foreach ($this->statuses as $name => $data) {
                $status = $this->getStatus($name);

                if ($status === self::STATUS_FINISHED) {
                    $this->processFinishedTask($name);
                } elseif ($status === self::STATUS_FAILED) {
                    $this->processFailedTask($name);
                }
            }
        });

        // Reset failure counters
        $this->loop->addPeriodicTimer(60, function () {
            foreach ($this->failureCounters as $name => $counter) {
                $this->failureCounters[$name] = 0;
            }
        });
    }

    private function processFinishedTask(string $name): void
    {
        $this->logger->debug('Daemon ":name" had finished, restarting', [
            ':name' => $name,
        ]);

        // Restart finished task
        $this->startSupervisedDaemon($name);
    }

    private function processFailedTask(string $name): void
    {
        // Increment failed attempts counter
        $this->failureCounters[$name]++;

        if ($this->isRetryLimitReached($this->failureCounters[$name])) {
            // Do not try to restart this failing task
            $this->setStatus($name, self::STATUS_STOPPED);

            // Warning for developers
            $this->logger->emergency('Daemon ":name" had failed :times times and was stopped', [
                ':name'  => $name,
                ':times' => $this->failureCounters[$name],
            ]);

            // No further processing
            return;
        }

        if (!$this->appEnv->inProductionMode()) {
            // Prevent high CPU usage on local errors
            sleep(3);
        }

        // Warning for developers
        $this->logger->warning('Daemon ":name" had failed and will be restarted immediately', [
            ':name' => $name,
//            ':times' => $this->failureCounters[$name],
        ]);

        // Restart failed task
        $this->startSupervisedDaemon($name);
    }

    private function restartStopped(): void
    {
        if ($this->isStoppingDaemons) {
            return;
        }

        // Trying to restart failed daemons
        foreach ($this->filterStatus(self::STATUS_STOPPED) as $name) {
            $this->startSupervisedDaemon($name, true);
        }
    }

    private function stopAll(): void
    {
        if ($this->isStoppingDaemons) {
            return;
        }

        $this->isStoppingDaemons = true;

        $this->logger->debug('Shutting down daemons');

        // Trying to restart failed daemons
        foreach ($this->filterStatus(self::STATUS_RUNNING) as $name) {
            $this->stopSupervisedDaemon($name);
        }

        $this->logger->info('All daemons are stopped');

        $this->isStoppingDaemons = false;
    }

    public function stopDaemon(LoopInterface $loop): void
    {
        // Prevent auto-restart
        $this->isRunning = false;

        $this->stopAll();

        $this->logger->info('Supervisor is shutting down');
    }

    private function startSupervisedDaemon(string $name, bool $clearCounter = null): void
    {
        $ignoreStatuses = [
            self::STATUS_STARTING,
            self::STATUS_RUNNING,
        ];

        // Prevent duplicates
        if ($this->hasStatus($name) && in_array($this->getStatus($name), $ignoreStatuses, true)) {
            return;
        }

        $this->setStatus($name, self::STATUS_STARTING);

        $this->logger->debug('Starting ":name" daemon', [
            ':name' => $name,
        ]);

        $cmd = AbstractTask::getTaskCmd($this->appEnv, 'daemon:runner', [
            'name' => $name,
        ]);

        $docRoot = $this->appEnv->getDocRootPath();

        $process = new Process($cmd, $docRoot);

        $lock = $this->lockFactory->create($name);

        $process->on('exit', function ($exitCode, $termSignal) use ($name, $lock) {
            $this->logger->debug('Daemon ":name" exited with :code code and :signal signal', [
                ':name'   => $name,
                ':code'   => $exitCode ?? 'unknown',
                ':signal' => $termSignal ?? 'unknown',
            ]);

            $isOk = ($exitCode ?? 0) === 0;

            $this->checkLockReleased($lock);

            $this->setStatus($name, $isOk ? self::STATUS_FINISHED : self::STATUS_FAILED);
        });

        // Ensure task is running
        $this->loop->addPeriodicTimer(0.1, function (TimerInterface $timer) use ($name, $process) {
            if ($process->isRunning()) {
                $this->setStatus($name, self::STATUS_RUNNING);
                $this->loop->cancelTimer($timer);

                $this->logger->debug('Daemon ":name" started', [
                    ':name' => $name,
                ]);
            }
        });

        $startTimeout = Runner::START_TIMEOUT;

        $this->loop->addTimer($startTimeout, function () use ($name, $process, $startTimeout) {
            $status = $this->getStatus($name);

            if ($status === self::STATUS_STARTING && !$process->isRunning()) {
                $this->setStatus($name, self::STATUS_FAILED);

                $this->logger->warning('Can not start ":name" daemon in :timeout seconds', [
                    ':name'    => $name,
                    ':timeout' => $startTimeout,
                ]);
            }
        });

        if ($clearCounter) {
            // Clear failure counter
            $this->failureCounters[$name] = 0;
        }

        $process->start($this->loop);

        $this->setProcess($name, $process);
    }

    private function stopSupervisedDaemon(string $name): void
    {
        $this->logger->debug('Stopping ":name" daemon', [
            ':name' => $name,
        ]);

        $status = $this->getStatus($name);

        $ignoreStatuses = [
            self::STATUS_FINISHED,
            self::STATUS_STOPPING,
            self::STATUS_STOPPED,
            self::STATUS_FAILED,
        ];

        // Skip processes which are restarting already (race condition with event handler)
        if (in_array($status, $ignoreStatuses, true)) {
            $this->logger->debug('Daemon ":name" is stopped already, skipping', [
                ':name' => $name,
            ]);

            // Already stopping/stopped => nothing to do here
            return;
        }

        $this->setStatus($name, self::STATUS_STOPPING);

        $lock = $this->lockFactory->create($name);

        if (!$lock->isAcquired()) {
            throw new Exception('Daemon ":name" is running but has no acquired lock', [
                ':name' => $name,
            ]);
        }

        $process = $this->getProcess($name);

        $this->logger->debug('Sending "stop" signal to ":name" daemon with PID = :pid', [
            ':pid'  => $process->getPid(),
            ':name' => $name,
        ]);

        $stopTimeout = Runner::STOP_TIMEOUT + 1;

        // Send stop signal to the daemon
        $process->terminate(\SIGTERM);

        // Sync wait for process stop ()
        if (!$lock->waitForRelease($stopTimeout)) {
            $this->logger->warning('Daemon ":name" had not been stopped in :timeout seconds, force exit', [
                ':name'    => $name,
                ':timeout' => $stopTimeout,
            ]);

            // Kill daemon
            $process->terminate(\SIGKILL);

            // Release the lock (cleanup)
            $lock->release();
        }

        $this->setStatus($name, self::STATUS_STOPPED);

        $this->logger->debug('Daemon ":name" stopped', [
            ':name' => $name,
        ]);
    }

    private function getDefinedDaemons(): array
    {
        return \array_unique((array)$this->config->load(['daemons']));
    }

    private function checkLockReleased(Lock $lock): bool
    {
        if ($lock->isAcquired()) {
            // Something went wrong on the daemon shutdown so we need to clear the lock
            $lock->release();

            // Warning for developers
            $this->logger->warning('Lock ":name" had not been released by the daemon:runner task', [
                ':name' => \basename($lock->getPath()),
            ]);
        }

        return true;
    }

    private function setStatus(string $name, string $status): void
    {
        $this->statuses[$name]['status'] = $status;

        $this->logger->debug('Setting status ":status" for daemon ":name"', [
            ':name'   => $name,
            ':status' => $status,
        ]);
    }

    private function getStatus(string $name): string
    {
        return $this->statuses[$name]['status'];
    }

    private function hasStatus(string $name): bool
    {
        return isset($this->statuses[$name]['status']);
    }

    private function setProcess(string $name, Process $proc): void
    {
        $this->statuses[$name]['process'] = $proc;
    }

    private function getProcess(string $name): Process
    {
        return $this->statuses[$name]['process'];
    }

    private function filterStatus(string $statusName): \Generator
    {
        foreach ($this->statuses as $taskName => $data) {
            $status = $this->getStatus($taskName);

            if ($status === $statusName) {
                yield $taskName;
            }
        }
    }

    private function isRetryLimitReached(int $count): bool
    {
        // Retry until it will be fixed in dev mode
        if ($this->appEnv->inDevelopmentMode()) {
            return false;
        }

        return $count > self::RETRY_LIMIT;
    }
}
