<?php

declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Daemon\DaemonException;
use BetaKiller\Daemon\DaemonFactory;
use BetaKiller\Daemon\DaemonInterface;
use BetaKiller\Daemon\DaemonLockFactory;
use BetaKiller\Daemon\FsWatcher;
use BetaKiller\Daemon\ShutdownDaemonException;
use BetaKiller\Dev\MemoryProfiler;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Log\LoggerInterface;
use BetaKiller\ProcessLock\LockInterface;
use BetaKiller\Task\AbstractTask;
use Database;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Throwable;

use function basename;
use function gc_collect_cycles;
use function gc_enabled;
use function gc_mem_caches;
use function getmypid;
use function memory_get_peak_usage;
use function pcntl_async_signals;
use function React\Async\await;
use function React\Promise\Timer\timeout;
use function ucfirst;

use const SIGHUP;
use const SIGINT;
use const SIGPROF;
use const SIGQUIT;
use const SIGTERM;
use const SIGUSR1;

final class Runner extends AbstractTask
{
    private const ARG_NAME = 'name';

    public const SIGNAL_PROFILE  = SIGPROF;
    public const SIGNAL_SHUTDOWN = SIGUSR1;

    private const STATUS_LOADING  = 'loading';
    private const STATUS_STARTING = 'starting';
    private const STATUS_RUNNING  = 'started';
    private const STATUS_STOPPING = 'stopping';
    private const STATUS_STOPPED  = 'stopped';
    private const STATUS_SHUTDOWN = 'shutdown';

    /**
     * @var \BetaKiller\Daemon\DaemonFactory
     */
    private DaemonFactory $daemonFactory;

    /**
     * @var \BetaKiller\Daemon\DaemonLockFactory
     */
    private DaemonLockFactory $lockFactory;

    /**
     * @var \BetaKiller\Env\AppEnvInterface
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
     * @var \BetaKiller\Daemon\DaemonInterface|null
     */
    private ?DaemonInterface $daemon = null;

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
     * @var \React\EventLoop\TimerInterface|null
     */
    private ?TimerInterface $idleTimer = null;

    /**
     * @var \React\EventLoop\TimerInterface|null
     */
    private ?TimerInterface $stopTimer = null;

    private bool $shutdownRequested = false;

    /**
     * @var int
     */
    private int $maxMemoryIncrease;

    /**
     * @var \BetaKiller\Dev\MemoryProfiler
     */
    private MemoryProfiler $memProf;

    /**
     * @var int
     */
    private int $exitCode = DaemonInterface::EXIT_CODE_OK;

    /**
     * Run constructor.
     *
     * @param \BetaKiller\Daemon\DaemonFactory     $daemonFactory
     * @param \BetaKiller\Daemon\DaemonLockFactory $lockFactory
     * @param \BetaKiller\Env\AppEnvInterface      $appEnv
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
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [
            $builder->string(self::ARG_NAME)->required(),
        ];
    }

    /**
     * @param \BetaKiller\Console\ConsoleInputInterface $params *
     *
     * @return void
     * @throws \BetaKiller\Daemon\DaemonException
     * @throws \BetaKiller\Task\TaskException
     */
    public function run(ConsoleInputInterface $params): void
    {
        $this->codename = ucfirst($params->getString('name'));

        if (!$this->codename) {
            throw new DaemonException('Daemon codename is not defined');
        }

        $this->daemon = $this->daemonFactory->create($this->codename);

        $this->acquireLock();

        try {
            $this->startDaemon();
            $this->bindTimers();

            // Based on the included files from startDaemon()
            $this->startFsWatcher();

            // Endless loop waiting for signals or stopDaemon() call
            $this->loop->run();
        } catch (Throwable $e) {
            $this->processException($e);
        }

        $this->releaseLock();

        $this->setStatus(self::STATUS_SHUTDOWN);

        $this->logger->debug('Daemon ":name" is exiting with code :code', [
            ':name' => $this->codename,
            ':code' => $this->exitCode,
        ]);

        exit($this->exitCode);
    }

    private function requestShutdown(): void
    {
        if ($this->shutdownRequested) {
            $this->logger->warning('Daemon ":name" shutdown already requested', [
                ':name' => $this->codename,
            ]);

            return;
        }

        $this->shutdownRequested = true;
    }

    /**
     * @throws \BetaKiller\Daemon\DaemonException
     */
    private function acquireLock(): void
    {
        $this->lock = $this->lockFactory->create($this->codename);

        $pid = getmypid();

        // Check if it is running already and exit if so
        if ($this->lock->isValid() && $this->lock->getPid() !== $pid) {
            // It is not normal to have multiple instances
            throw new DaemonException('Daemon ":name" is already running', [
                ':name' => $this->codename,
            ]);
        }

        if ($this->lock->isAcquired()) {
            // Something went wrong on the daemon shutdown, so we need to clear the lock
            $this->lock->release();

            // Warning for developers
            $this->logger->warning('Lock ":name" had not been released by the daemon:runner task', [
                ':name' => basename($this->lock->getPath()),
            ]);
        }

        if (!$this->lock->acquire($pid)) {
            throw new DaemonException('Can not acquire lock for daemon ":name"', [
                ':name' => $this->codename,
            ]);
        }
    }

    /**
     * @throws \BetaKiller\Daemon\DaemonException
     */
    private function releaseLock(): void
    {
        // Check if it is running already and exit if so
        if (!$this->lock->isValid()) {
            // It is not normal to have multiple instances
            throw new DaemonException('Daemon ":name" has no valid lock', [
                ':name' => $this->codename,
            ]);
        }

        if ($this->lock->release()) {
            $this->logger->debug('Daemon ":name" was unlocked', [
                ':name' => $this->codename,
            ]);
        } else {
            $this->logger->notice('Daemon ":name" is not locked', [
                ':name' => $this->codename,
            ]);
        }
    }

    /**
     * @throws \Throwable
     * @throws \BetaKiller\Daemon\DaemonException
     */
    private function startDaemon(): void
    {
        // Prevent sequential start calls
        if ($this->isStarting()) {
            throw new DaemonException('Daemon ":name" is already starting, please wait', [
                ':name' => $this->codename,
            ]);
        }

        $this->setStatus(self::STATUS_STARTING);

        $this->awaitTimeout($this->daemon->startDaemon($this->loop), $this->daemon->getStartupTimeout());

        $this->setStatus(self::STATUS_RUNNING);
    }

    /**
     * @throws \BetaKiller\Daemon\DaemonException
     * @throws \Throwable
     */
    private function stopDaemon(): void
    {
        // Prevent sequential stop calls
        if ($this->isStopping()) {
            throw new DaemonException('Daemon ":name" is already stopping, please wait', [
                ':name' => $this->codename,
            ]);
        }

        $this->setStatus(self::STATUS_STOPPING);

        $this->waitForIdleState();
        $this->awaitTimeout($this->daemon->stopDaemon($this->loop), $this->daemon->getShutdownTimeout());

        $this->setStatus(self::STATUS_STOPPED);
    }

    /**
     * @throws \Throwable
     */
    private function waitForIdleState(): void
    {
        $deferred = new Deferred();

        $this->idleTimer = $this->loop->addPeriodicTimer(0.5, function () use ($deferred) {
            if ($this->daemon->isIdle()) {
                $this->loop->cancelTimer($this->idleTimer);
                $deferred->resolve();
            }
        });

        $this->awaitTimeout($deferred->promise(), $this->daemon->getShutdownTimeout() / 2);
    }

    /**
     * @throws \Throwable
     */
    private function awaitTimeout(PromiseInterface $promise, int $timeout): void
    {
        await(timeout($promise, $timeout, $this->loop));
    }

    /**
     * @throws \BetaKiller\Daemon\DaemonException
     */
    private function bindTimers(): void
    {
        $this->addSignalHandlers();
        $this->addStopTimer();
        $this->addGcTimer();
        $this->addPingDatabaseHandler();
        $this->addFlushLogHandler();
        $this->startMemoryConsumptionGuard();
    }

    private function removeTimers(): void
    {
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

        // Stop idle watcher
        if ($this->idleTimer) {
            $this->loop->cancelTimer($this->idleTimer);
        }

        $this->logger->debug('Daemon ":name" timers removed', [
            ':name' => $this->codename,
        ]);
    }

    private function addStopTimer(): void
    {
        $this->stopTimer = $this->loop->addPeriodicTimer(0.5, function () {
            if (!$this->shutdownRequested) {
                return;
            }

            $this->loop->cancelTimer($this->stopTimer);

            $this->shutdown();
        });
    }

    private function shutdown(): void
    {
        $this->logger->debug('Daemon ":name" is shutting down', [
            ':name' => $this->codename,
        ]);

        try {
            $this->removeTimers();
            $this->stopFsWatcher();
            $this->stopDaemon();
        } catch (Throwable $e) {
            // Force kill + notify supervisor about problem via non-zero exit status
            $this->processException($e);

            $this->logger->alert('Daemon ":name" had not stopped, force exit applied', [
                ':name' => $this->codename,
            ]);
        } finally {
            $this->loop->stop();
        }
    }

    /**
     * @throws \BetaKiller\Daemon\DaemonException
     */
    private function addGcTimer(): void
    {
        if (!gc_enabled()) {
            throw new DaemonException('GC disabled but it is required for proper daemons processing');
        }

        // Force GC to be called periodically
        // @see https://github.com/ratchetphp/Ratchet/issues/662
        $this->gcTimer = $this->loop->addPeriodicTimer(60, static function () {
            gc_collect_cycles();
            gc_mem_caches();
        });
    }

    private function processException(Throwable $e): void
    {
        if (!$e instanceof ShutdownDaemonException) {
            // Something wrong is going on here
            LoggerHelper::logRawException($this->logger, $e);
            $this->exitCode = DaemonInterface::EXIT_CODE_FAILED;
        }
    }

    private function addSignalHandlers(): void
    {
        pcntl_async_signals(true);

        $shutdownHandler = function (int $signal) {
            $this->logger->debug('Received signal ":value" for ":name" daemon', [
                ':value' => $signal,
                ':name'  => $this->codename,
            ]);

            $this->requestShutdown();
        };

        /**
         * @see https://stackoverflow.com/a/38991496
         */
        $this->loop->addSignal(SIGHUP, $shutdownHandler);
        $this->loop->addSignal(SIGINT, $shutdownHandler);
        $this->loop->addSignal(SIGQUIT, $shutdownHandler);

        if ($this->daemon->isShutdownOnSigTermAllowed()) {
            // Stop on SIGTERM
            $this->loop->addSignal(SIGTERM, $shutdownHandler);
        } else {
            // Install no-op handler instead
            $this->loop->addSignal(SIGTERM, function () {
                $this->logger->notice('SIGTERM sent to ":name" Daemon, but that signal is not supported', [
                    ':name' => $this->codename,
                ]);
                // NO OP
            });

            // Shutdown on default signal
            $this->loop->addSignal(self::SIGNAL_SHUTDOWN, $shutdownHandler);
        }

        $this->loop->addSignal(self::SIGNAL_PROFILE, function () {
            $this->dumpMemory();
        });
    }

    private function addPingDatabaseHandler(): void
    {
        $this->pingDbTimer = $this->loop->addPeriodicTimer(5, static function () {
            foreach (Database::pingAll() as $error) {
                LoggerHelper::logRawException($this->logger, $error);
            }
        });
    }

    private function addFlushLogHandler(): void
    {
        // Flush Monolog buffers to prevent memory leaks
        $this->flushLogsTimer = $this->loop->addPeriodicTimer(5, function () {
            $this->logger->flushBuffers();
        });
    }

    private function startMemoryConsumptionGuard(): void
    {
        $initialUsage = memory_get_peak_usage(true);
        $maxUsage     = $initialUsage + $this->maxMemoryIncrease;

        $this->memoryConsumptionTimer = $this->loop->addPeriodicTimer(1, function () use ($maxUsage) {
            // Prevent calls on startup and kills during processing
            if (!$this->isRunning() || !$this->daemon->isIdle()) {
                return;
            }

            $isMemoryLeaking = memory_get_peak_usage(true) > $maxUsage;

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

            $this->requestShutdown();
        });
    }

    private function dumpMemory(): void
    {
        $this->memProf->dump(
            implode('.', [
                'daemon',
                $this->codename,
            ])
        );
    }

    private function setStatus(string $value): void
    {
        $this->status = $value;

        $this->logger->debug('Daemon ":name" status set to ":status"', [
            ':name'   => $this->codename,
            ':status' => $this->status,
        ]);
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

    /**
     * @throws \BetaKiller\Exception
     */
    private function startFsWatcher(): void
    {
        if (!$this->appEnv->inDevelopmentMode() || !$this->daemon->isRestartOnFsChangesAllowed()) {
            return;
        }

        $this->fsWatcher->start($this->loop, function (string $path) {
            // Prevent calls during startup/shutdown
            if (!$this->isRunning()) {
                return;
            }

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
            $this->requestShutdown();
        });
    }

    private function stopFsWatcher(): void
    {
        // Stop FS watcher if enabled
        $this->fsWatcher->stop($this->loop);

        $this->logger->debug('Daemon ":name" filesystem watch stopped', [
            ':name' => $this->codename,
        ]);
    }
}
