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

    public const RETRY_LIMIT   = 3;
    public const RELOAD_SIGNAL = \SIGUSR1;

    private const STATUS_STARTING = 'starting';
    private const STATUS_RUNNING  = 'running';
    private const STATUS_FINISHED = 'finished';
    private const STATUS_STOPPING = 'stopping';
    private const STATUS_STOPPED  = 'stopped';
    private const STATUS_FAILED   = 'failed';

    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private $config;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var bool
     */
    private $isHuman;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var int[]
     */
    private $failureCounter = [];

    /**
     * @var mixed[][]
     */
    private $statuses = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Daemon\LockFactory
     */
    private $lockFactory;

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

    public function start(LoopInterface $loop): void
    {
        $this->loop    = $loop;
        $this->isHuman = $this->appEnv->isHuman();

        // Reload signal => hot restart
        $loop->addSignal(self::RELOAD_SIGNAL, function () {
            $this->logger->debug('Reloading supervisor');
            $this->restartStopped();
        });

        $this->addSupervisorTimer();

        foreach ($this->getDefinedDaemons() as $codename) {
            $this->startDaemon($codename, true);
        }
    }

    private function addSupervisorTimer(): void
    {
        $this->loop->addPeriodicTimer(0.1, function () {
            foreach ($this->statuses as $name => $data) {
                $status = $this->getStatus($name);

                if ($status === self::STATUS_FINISHED) {
                    $this->processFinishedTask($name);
                } elseif ($status === self::STATUS_FAILED) {
                    $this->processFailedTask($name);
                }
            }
        });
    }

    private function processFinishedTask(string $name): void
    {
        $this->logger->notice('Daemon ":name" had finished, restarting', [
            ':name' => $name,
        ]);

        // Restart finished task
        $this->startDaemon($name);
    }

    private function processFailedTask(string $name): void
    {
        // Increment failed attempts counter
        $this->failureCounter[$name]++;

        if ($this->failureCounter[$name] > self::RETRY_LIMIT) {
            // Do not try to restart this failing task
            $this->setStatus($name, self::STATUS_STOPPED);

            // Warning for developers
            $this->logger->emergency('Daemon ":name" had failed :times times and was stopped', [
                ':name'  => $name,
                ':times' => $this->failureCounter[$name],
            ]);

            // No further processing
            return;
        }

        // Warning for developers
        $this->logger->warning('Daemon ":name" had failed :times times and will be restarted immediately', [
            ':name'  => $name,
            ':times' => $this->failureCounter[$name],
        ]);

        // Restart failed task
        $this->startDaemon($name);
    }

    public function restartStopped(): void
    {
        // Trying to restart failed daemons
        foreach ($this->filterStatus(self::STATUS_STOPPED) as $name) {
            $this->startDaemon($name, true);
        }
    }

    public function stop(): void
    {
        $this->logger->debug('Shutting down daemons');

        foreach ($this->filterStatus(self::STATUS_RUNNING) as $name) {
            $this->stopDaemon($name);
        }

        $this->logger->info('All daemons are stopped, supervisor is shutting down');
    }

    private function startDaemon(string $name, bool $clearCounter = null): void
    {
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
                ':code'   => $termSignal ?? 'unknown',
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

                $this->logger->notice('Daemon ":name" started', [
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
            $this->failureCounter[$name] = 0;
        }

        $process->start($this->loop);

        $this->setProcess($name, $process);
    }

    private function stopDaemon(string $name): void
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
        if ($lock->waitForRelease($stopTimeout)) {
            $this->setStatus($name, self::STATUS_STOPPED);

            $this->logger->notice('Daemon ":name" stopped', [
                ':name' => $name,
            ]);
        } else {
            $this->logger->warning('Daemon ":name" had not been stopped in :timeout seconds, force exit', [
                ':name'    => $name,
                ':timeout' => $stopTimeout,
            ]);
        }
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
}
