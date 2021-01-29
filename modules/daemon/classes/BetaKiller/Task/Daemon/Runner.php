<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Daemon\AbstractDaemon;
use BetaKiller\Daemon\DaemonFactory;
use BetaKiller\Daemon\DaemonInterface;
use BetaKiller\Daemon\DaemonLockFactory;
use BetaKiller\Daemon\FsWatcher;
use BetaKiller\Daemon\ShutdownDaemonException;
use BetaKiller\Dev\MemoryProfiler;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Log\LoggerInterface;
use BetaKiller\ProcessLock\LockInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use Database;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use function Clue\React\Block\await;
use function React\Promise\reject;
use function React\Promise\Timer\timeout;

final class Runner extends AbstractTask
{
    public const SIGNAL_PROFILE = \SIGPROF;

    private const STATUS_LOADING  = 'loading';
    private const STATUS_STARTING = 'starting';
    private const STATUS_RUNNING  = 'started';
    private const STATUS_STOPPING = 'stopping';

    /**
     * @var \BetaKiller\Daemon\DaemonFactory
     */
    private DaemonFactory $daemonFactory;

    /**
     * @var \BetaKiller\Daemon\DaemonLockFactory
     */
    private DaemonLockFactory $lockFactory;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * @var \BetaKiller\Daemon\FsWatcher
     */
    private FsWatcher $fsWatcher;

    /**
     * @var \BetaKiller\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var string
     */
    private string $codename;

    /**
     * @var \BetaKiller\Daemon\DaemonInterface
     */
    private DaemonInterface $daemon;

    /**
     * @var \React\EventLoop\LoopInterface
     */
    private LoopInterface $loop;

    /**
     * @var \BetaKiller\ProcessLock\LockInterface
     */
    private LockInterface $lock;

    /**
     * @var string
     */
    private string $status = self::STATUS_LOADING;

    /**
     * @var \React\EventLoop\TimerInterface|null
     */
    private ?TimerInterface $memoryConsumptionTimer = null;

    /**
     * @var \React\EventLoop\TimerInterface|null
     */
    private ?TimerInterface $pingDbTimer = null;

    /**
     * @var \React\EventLoop\TimerInterface|null
     */
    private ?TimerInterface $flushLogsTimer = null;

    /**
     * @var \React\EventLoop\TimerInterface|null
     */
    private ?TimerInterface $gcTimer = null;

    /**
     * @var int
     */
    private int $maxMemoryIncrease;

    /**
     * @var \BetaKiller\Dev\MemoryProfiler
     */
    private MemoryProfiler $memProf;

    /**
     * Run constructor.
     *
     * @param \BetaKiller\Daemon\DaemonFactory     $daemonFactory
     * @param \BetaKiller\Daemon\DaemonLockFactory $lockFactory
     * @param \BetaKiller\Helper\AppEnvInterface   $appEnv
     * @param \BetaKiller\Daemon\FsWatcher         $fsWatcher
     * @param \BetaKiller\Dev\MemoryProfiler       $memProf
     * @param \React\EventLoop\LoopInterface       $loop
     * @param \BetaKiller\Log\LoggerInterface      $logger
     */
    public function __construct(
        DaemonFactory $daemonFactory,
        DaemonLockFactory $lockFactory,
        AppEnvInterface $appEnv,
        FsWatcher $fsWatcher,
        MemoryProfiler $memProf,
        LoopInterface $loop,
        LoggerInterface $logger
    ) {
        $this->daemonFactory = $daemonFactory;
        $this->lockFactory   = $lockFactory;
        $this->appEnv        = $appEnv;
        $this->fsWatcher     = $fsWatcher;
        $this->memProf       = $memProf;
        $this->loop          = $loop;
        $this->logger        = $logger;


        $this->maxMemoryIncrease = (int)$appEnv->getEnvVariable('DAEMON_MAX_MEMORY_USAGE', true);

        parent::__construct();
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [
            'name' => null,
        ];
    }

    public function run(): void
    {
        $this->codename = \ucfirst((string)$this->getOption('name', true));

        if (!$this->codename) {
            throw new \LogicException('Daemon codename is not defined');
        }

        $this->lock = $this->lockFactory->create($this->codename);

        // Check if it is running already and exit if so
        if ($this->lock->isValid()) {
            $this->logger->warning('Daemon ":name" is already running', [
                ':name' => $this->codename,
            ]);

            // It is not normal to have multiple instances
            $this->shutdown(DaemonInterface::EXIT_CODE_FAILED);
        }

        if ($this->lock->isAcquired()) {
            // Something went wrong on the daemon shutdown so we need to clear the lock
            $this->lock->release();

            // Warning for developers
            $this->logger->warning('Lock ":name" had not been released by the daemon:runner task', [
                ':name' => \basename($this->lock->getPath()),
            ]);
        }

        if (!$this->lock->acquire(\getmypid())) {
            throw new TaskException('Can not acquire lock for daemon ":name"', [
                ':name' => $this->codename,
            ]);
        }

        if (\gc_enabled()) {
            // Force GC to be called periodically
            // @see https://github.com/ratchetphp/Ratchet/issues/662
            $this->gcTimer = $this->loop->addPeriodicTimer(60, static function () {
                \gc_collect_cycles();
                \gc_mem_caches();
            });
        } else {
            $this->logger->warning('GC disabled but it is required for proper daemons processing');
        }

        $this->addSignalHandlers();

        $this->pingDB();

        // Flush Monolog buffers to prevent memory leaks
        $this->flushLogsTimer = $this->loop->addPeriodicTimer(10, function () {
            $this->logger->flushBuffers();
        });

        $this->startMemoryConsumptionGuard();

        await($this->start(), Factory::create(), AbstractDaemon::STARTUP_TIMEOUT + 2);

        // Based on the included files
        if ($this->appEnv->inDevelopmentMode()) {
            $this->startFsWatcher($this->loop);
        }

        // Endless loop waiting for signals or exit()
        $this->loop->run();

        // Normal shutdown
        $this->shutdown(DaemonInterface::EXIT_CODE_OK);
    }

    private function start(): PromiseInterface
    {
        // Prevent sequential start calls
        if ($this->isStarting()) {
            $this->logger->notice('Daemon ":name" is already starting, please wait', [
                ':name' => $this->codename,
            ]);

            return reject();
        }

        $this->setStatus(self::STATUS_STARTING);

        $deferred = new Deferred();
        $promise  = $deferred->promise();

        $promise->done(function () {
            $this->setStatus(self::STATUS_RUNNING);

            $this->logger->debug('Daemon ":name" is started', [
                ':name' => $this->codename,
            ]);
        });

        $promise->otherwise(function (\Throwable $e = null) {
            $this->setStatus(self::STATUS_STOPPING);

            $this->processException($e);
        });

        try {
            $this->daemon = $this->daemonFactory->create($this->codename);

            $this->daemon->startDaemon($this->loop)->then(
                static function () use ($deferred) {
                    $deferred->resolve();
                },
                static function () use ($deferred) {
                    $deferred->reject();
                },
            );
        } catch (\Throwable $e) {
            $deferred->reject($e);
        }

        return $promise;
    }

    private function stop(bool $isFailed): PromiseInterface
    {
        // Prevent sequential stop calls
        if ($this->isStopping()) {
            $this->logger->notice('Daemon ":name" is already stopping, please wait', [
                ':name' => $this->codename,
            ]);

            return reject();
        }

        $this->setStatus(self::STATUS_STOPPING);

        $deferred = new Deferred();
        $promise  = $deferred->promise();

        $promise->done(function () use ($isFailed) {
            $this->logger->debug('Daemon ":name" was stopped', [
                ':name' => $this->codename,
            ]);

            $exitCode = $isFailed ? DaemonInterface::EXIT_CODE_FAILED : DaemonInterface::EXIT_CODE_OK;

            // Daemon would be restarted by supervisor
            $this->shutdown($exitCode);
        });

        $promise->otherwise(function (\Throwable $e = null) {
            $this->logger->alert('Daemon ":name" had not stopped, force exit applied', [
                ':name' => $this->codename,
            ]);

            // Timeout => force kill + notify supervisor about problem via non-zero exit status
            $this->processException($e);
        });

        $this->loop->addPeriodicTimer(0.5, function (TimerInterface $pollTimer) use ($deferred) {
            // Race condition check
            if (!$this->daemon) {
                $deferred->reject();

                return;
            }

            if (!$this->daemon->isIdle()) {
                return;
            }

            $this->loop->cancelTimer($pollTimer);

            try {
                $this->daemon->stopDaemon($this->loop)->then(
                    static function () use ($deferred) {
                        $deferred->resolve();
                    },
                    static function () use ($deferred) {
                        $deferred->reject();
                    }
                );
            } catch (\Throwable $e) {
                $deferred->reject($e);
            }
        });

        return timeout($promise, AbstractDaemon::SHUTDOWN_TIMEOUT + 2, $this->loop);
    }

    private function shutdown(int $exitCode): void
    {
        $this->unlock();

        // Stop garbage collection trigger
        if ($this->gcTimer) {
            $this->loop->cancelTimer($this->gcTimer);
        }

        // Stop memory usage monitor
        if ($this->memoryConsumptionTimer) {
            $this->loop->cancelTimer($this->memoryConsumptionTimer);
        }

        // Stop DB connection
        if ($this->pingDbTimer) {
            $this->loop->cancelTimer($this->pingDbTimer);
        }

        // Stop logger flusher
        if ($this->flushLogsTimer) {
            $this->loop->cancelTimer($this->flushLogsTimer);
        }

        // Stop FS watcher if enabled
        $this->fsWatcher->stop();

        $this->logger->debug('Daemon ":name" has exited with exit code :code', [
            ':name' => $this->codename,
            ':code' => $exitCode,
        ]);

        exit($exitCode);
    }

    private function processException(?\Throwable $e): void
    {
        if (!$e) {
            $this->shutdown(DaemonInterface::EXIT_CODE_FAILED);
        }

        if ($e instanceof ShutdownDaemonException) {
            // Normal shutdown
            $this->shutdown(DaemonInterface::EXIT_CODE_OK);
        } else {
            // Something wrong is going on here
            LoggerHelper::logRawException($this->logger, $e);
            $this->shutdown(DaemonInterface::EXIT_CODE_FAILED);
        }
    }

    private function addSignalHandlers(): void
    {
        \pcntl_async_signals(true);

        $signalCallable = function (int $signal) {
            $this->logger->debug('Received signal ":value" for ":name" daemon', [
                ':value' => $signal,
                ':name'  => $this->codename,
            ]);

            $this->stop(false);
        };

        /**
         * @see https://stackoverflow.com/a/38991496
         */
        $this->loop->addSignal(\SIGHUP, $signalCallable);
        $this->loop->addSignal(\SIGINT, $signalCallable);
        $this->loop->addSignal(\SIGQUIT, $signalCallable);
        $this->loop->addSignal(\SIGTERM, $signalCallable);

        $this->loop->addSignal(self::SIGNAL_PROFILE, function () {
            $this->dumpMemory();
        });
    }

    private function pingDB(): void
    {
        $this->pingDbTimer = $this->loop->addPeriodicTimer(60, static function () {
            Database::pingAll();
        });
    }

    private function startMemoryConsumptionGuard(): void
    {
        $initialUsage = \memory_get_peak_usage(true);
        $maxUsage     = $initialUsage + $this->maxMemoryIncrease;

        $this->memoryConsumptionTimer = $this->loop->addPeriodicTimer(1, function () use ($maxUsage) {
            // Prevent calls on startup and kills during processing
            if (!$this->isRunning() || !$this->daemon->isIdle()) {
                return;
            }

            $isMemoryLeaking = \memory_get_peak_usage(true) > $maxUsage;

            if (!$isMemoryLeaking) {
                return;
            }

            // Stop timer
            $this->loop->cancelTimer($this->memoryConsumptionTimer);

            // Dump CacheGrind profile
            $this->dumpMemory();

            $this->logger->notice('Daemon ":name" consumes too much memory, restarting', [
                ':name' => $this->codename,
            ]);

            $this->stop(true);
        });
    }

    private function dumpMemory(): void
    {
        $this->memProf->dump(implode('.', [
            'daemon',
            $this->codename,
        ]));
    }

    private function unlock(): void
    {
        try {
            if ($this->lock->release()) {
                $this->logger->debug('Daemon ":name" was unlocked', [
                    ':name' => $this->codename,
                ]);
            } else {
                $this->logger->notice('Daemon ":name" is not locked', [
                    ':name' => $this->codename,
                ]);
            }
        } catch (\Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        }
    }

    private function setStatus(string $value): void
    {
        $this->status = $value;
    }

    private function isStarting(): bool
    {
        return $this->status === self::STATUS_STARTING;
    }

    private function isRunning(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }

    private function isStopping(): bool
    {
        return $this->status === self::STATUS_STOPPING;
    }

    private function startFsWatcher(LoopInterface $loop): void
    {
        $this->fsWatcher->start($loop, function (string $path) {
            $ext = pathinfo($path, PATHINFO_EXTENSION);

            $isLoaded = in_array($path, \get_included_files(), true);

            $this->logger->debug('path = :path, isLoaded = :loaded, ext = :ext', [
                ':path'   => $path,
                ':ext'    => $ext,
                ':loaded' => $isLoaded ? 'true' : 'false',
            ]);

            // Skip changes for files which are not loaded yet
            if ($ext === 'php' && !$isLoaded) {
                return;
            }

            $this->logger->info('Restarting daemon ":name" after FS changes', [
                ':name' => $this->codename,
            ]);

            // Restart daemon (auto-restart after stop)
            $this->stop(false);
        });
    }
}
