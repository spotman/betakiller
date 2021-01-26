<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\ProcessLock\LockInterface;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;
use React\ChildProcess\Process;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use function Clue\React\Block\await;
use function React\Promise\all;
use function React\Promise\reject;
use function React\Promise\resolve;
use function React\Promise\Timer\timeout;

final class SupervisorDaemon extends AbstractDaemon
{
    public const CODENAME = 'Supervisor';

    public const RETRY_LIMIT    = 60;
    public const SIGNAL_RELOAD  = \SIGUSR1;
    public const SIGNAL_RESTART = \SIGUSR2;

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
     * @var \BetaKiller\Daemon\DaemonLockFactory
     */
    private DaemonLockFactory $lockFactory;

    private bool $isRunning = false;

    private bool $isStoppingDaemons = false;

    private bool $isRestartingDaemons = false;

    /**
     * Supervisor constructor.
     *
     * @param \BetaKiller\Config\ConfigProviderInterface $config
     * @param \BetaKiller\Helper\AppEnvInterface         $appEnv
     * @param \BetaKiller\Daemon\DaemonLockFactory       $lockFactory
     * @param \Psr\Log\LoggerInterface                   $logger
     */
    public function __construct(
        ConfigProviderInterface $config,
        AppEnvInterface $appEnv,
        DaemonLockFactory $lockFactory,
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
        $loop->addSignal(self::SIGNAL_RELOAD, function () {
            $this->logger->debug('Reloading stopped daemons');
            $this->restartStopped();
        });

        // Restart signal => hot restart
        $loop->addSignal(self::SIGNAL_RESTART, function () {
            $this->logger->debug('Restarting all daemons');
            $this->restartRunning();
        });

        $this->addSupervisorTimer();

        timeout($this->startAll(), AbstractDaemon::STARTUP_TIMEOUT, Factory::create())
            ->done(function () {
                $this->isRunning = true;
            });
    }

    private function startAll(): PromiseInterface
    {
        $startPromises = [];

        foreach ($this->getDefinedDaemons() as $codename) {
            $promise = $this->startSupervisedDaemon($codename, true);

            $promise->then(
                function () use ($codename) {
                    $this->logger->debug('Started daemon ":name"', [
                        ':name' => $codename,
                    ]);
                },
                function () use ($codename) {
                    $this->logger->error('Daemon ":name" start failed', [
                        ':name' => $codename,
                    ]);
                },
            );

            $startPromises[] = $promise;
        }

        return all($startPromises);
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

    private function restartRunning(): PromiseInterface
    {
        if ($this->isStoppingDaemons || $this->isRestartingDaemons) {
            return reject();
        }

        $this->isRestartingDaemons = true;

        $restartPromises = [];

        // Trying to restart failed daemons
        foreach ($this->filterStatus(self::STATUS_RUNNING) as $name) {
            // Start only after successful stop
            $restartPromises[] = $this->stopSupervisedDaemon($name);
        }

        $allPromise = all($restartPromises);

        $allPromise->done(function () {
            $this->logger->info('All daemons are restarted');
        });

        $allPromise->otherwise(function () {
            $this->logger->warning('Daemons restart failed');
        });

        $allPromise->always(function() {
            $this->isRestartingDaemons = false;
        });

        return $allPromise;
    }

    private function stopAll(): PromiseInterface
    {
        if ($this->isStoppingDaemons || $this->isRestartingDaemons) {
            return reject();
        }

        $this->isStoppingDaemons = true;

        $this->logger->debug('Shutting down daemons');

        $stopPromises = [];

        // Trying to restart failed daemons
        foreach ($this->filterStatus(self::STATUS_RUNNING) as $name) {
            $stopPromises[] = $this->stopSupervisedDaemon($name);
        }

        $allPromise = all($stopPromises);

        $allPromise->done(function () {
            $this->logger->info('All daemons are stopped');
        });

        $allPromise->otherwise(function () {
            $this->logger->warning('Daemons stop failed');
        });

        $allPromise->always(function() {
            $this->isStoppingDaemons = false;
        });

        return $allPromise;
    }

    public function stopDaemon(LoopInterface $loop): void
    {
        // Prevent auto-restart
        $this->isRunning = false;

        await($this->stopAll(), $loop);

        $this->logger->info('Supervisor is shutting down');
    }

    private function startSupervisedDaemon(string $name, bool $clearCounter = null): PromiseInterface
    {
        $ignoreStatuses = [
            self::STATUS_STARTING,
            self::STATUS_RUNNING,
        ];

        // Prevent duplicates
        if ($this->hasStatus($name) && in_array($this->getStatus($name), $ignoreStatuses, true)) {
            return resolve();
        }

        $startTimeout = AbstractDaemon::STARTUP_TIMEOUT;

        $deferred = new Deferred;
        $promise  = timeout($deferred->promise(), $startTimeout, $this->loop);

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

        // Listen to process stop
        $process->on('exit', function ($exitCode, $termSignal) use ($deferred, $name, $lock) {
            $this->logger->debug('Daemon ":name" exited with :code code and :signal signal', [
                ':name'   => $name,
                ':code'   => $exitCode ?? 'unknown',
                ':signal' => $termSignal ?? 'unknown',
            ]);

            $isOk = ($exitCode ?? 0) === 0;

            $this->checkLockReleased($lock);

            switch (true) {
                // Exit code is non-normal
                // Or exit code is OK but process was starting
                case !$isOk:
                case $this->isStatus($name, self::STATUS_STARTING):
                    $this->setStatus($name, self::STATUS_FAILED);
                    $deferred->reject();
                    break;

                // Exit code is OK and process was running
                case $this->isStatus($name, self::STATUS_RUNNING):
                    $this->setStatus($name, self::STATUS_FINISHED);
                    break;

                // Exit code is OK and process was stopping
                case $this->isStatus($name, self::STATUS_STOPPING):
                    $this->setStatus($name, self::STATUS_STOPPED);
                    break;

                default:
                    throw new \LogicException(sprintf('Unknown daemon status upon exit "%s"', $this->getStatus($name)));
            }
        });

        if ($clearCounter) {
            // Clear failure counter
            $this->failureCounters[$name] = 0;
        }

        $process->start($this->loop);

        $this->setProcess($name, $process);

        // Ensure task is running
        $pollingTimer = $this->loop->addPeriodicTimer(0.5, static function () use ($deferred, $process) {
            if ($process->getPid()) {
                $deferred->resolve();
            }
        });

        // On successful startup
        $promise->done(function () use ($name, $pollingTimer) {
            $this->loop->cancelTimer($pollingTimer);

            $this->setStatus($name, self::STATUS_RUNNING);

            $this->logger->info('Daemon ":name" started', [
                ':name' => $name,
            ]);
        });

        // On startup error
        $promise->otherwise(function () use ($name, $startTimeout, $pollingTimer) {
            $this->loop->cancelTimer($pollingTimer);

            $this->setStatus($name, self::STATUS_FAILED);

            $this->logger->warning('Can not start ":name" daemon in :timeout seconds', [
                ':name'    => $name,
                ':timeout' => $startTimeout,
            ]);
        });

        return $promise;
    }

    private function stopSupervisedDaemon(string $name): PromiseInterface
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
            return resolve();
        }

        $deferred = new Deferred();

        $this->setStatus($name, self::STATUS_STOPPING);

        $process = $this->getProcess($name);

        $lock = $this->lockFactory->create($name);

        if ($process->isRunning() && !$lock->isAcquired()) {
            $this->logger->warning('Daemon ":name" is running but has no acquired lock', [
                ':name' => $name,
            ]);
        }

        $stopTimeout = AbstractDaemon::SHUTDOWN_TIMEOUT + 1;

        $pollingTimer = $this->loop->addPeriodicTimer($stopTimeout,
            function (TimerInterface $timer) use ($deferred, $process) {
                if ($process->isRunning()) {
                    return;
                }

                // Stop polling
                $this->loop->cancelTimer($timer);

                // Notify about successful daemon stop
                $deferred->resolve();
            });

        // Stop timeout timer
        $timeoutTimer = $this->loop->addTimer($stopTimeout,
            function () use ($pollingTimer, $deferred, $name, $process, $stopTimeout) {
                // Stop polling
                $this->loop->cancelTimer($pollingTimer);

                if (!$process->isRunning()) {
                    // Notify about successful daemon stop
                    $deferred->resolve();

                    return;
                }

                $this->logger->warning('Daemon ":name" had not been stopped in :timeout seconds, force kill', [
                    ':name'    => $name,
                    ':timeout' => $stopTimeout,
                ]);

                // Force kill
                if (!$process->terminate(\SIGKILL)) {
                    $this->logger->warning('Daemon ":name" had not been killed (signaling failed)', [
                        ':name' => $name,
                    ]);

                    // No signaling => failed
                    $deferred->reject();
                }
            });

        // Async wait for process to be stopped
        $process->on('exit', function () use ($deferred, $timeoutTimer) {
            $this->loop->cancelTimer($timeoutTimer);

            // Notify about successful daemon stop
            $deferred->resolve();
        });

        /**
         * Actual stop processing is placed below
         */

        $this->logger->debug('Sending "stop" signal to ":name" daemon with PID = :pid', [
            ':pid'  => $process->getPid(),
            ':name' => $name,
        ]);

        // Send stop signal to the daemon
        if (!$process->terminate(\SIGTERM)) {
            $this->logger->warning('Daemon ":name" had not been stopped (signaling failed)', [
                ':name' => $name,
            ]);

            // No signaling => failed
            $deferred->reject();
        }

        $promise = $deferred->promise();

        $promise->done(function () use ($name) {
            $this->setStatus($name, self::STATUS_STOPPED);

            // Clear counter for a fresh next start
            $this->failureCounters[$name] = 0;
        });

        $promise->otherwise(function () use ($process, $name) {
            $this->setStatus($name, $process->isRunning() ? self::STATUS_RUNNING : self::STATUS_FAILED);
        });

        $promise->always(function () use ($timeoutTimer, $pollingTimer) {
            $this->loop->cancelTimer($pollingTimer);
            $this->loop->cancelTimer($timeoutTimer);
        });

        return $promise;
    }

    private function getDefinedDaemons(): array
    {
        return \array_unique((array)$this->config->load(['daemons']));
    }

    private function checkLockReleased(LockInterface $lock): bool
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

    private function isStatus(string $name, string $status): bool
    {
        return $this->hasStatus($name) ? $this->getStatus($name) === $status : false;
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
