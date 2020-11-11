<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Daemon\DaemonFactory;
use BetaKiller\Daemon\DaemonInterface;
use BetaKiller\Daemon\FsWatcher;
use BetaKiller\Daemon\DaemonLockFactory;
use BetaKiller\Daemon\ShutdownDaemonException;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\ProcessLock\LockInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use Database;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

final class Runner extends AbstractTask
{
    public const START_TIMEOUT = 5;
    public const STOP_TIMEOUT  = 15;

    public const MAX_MEMORY_RATIO  = 20;

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
     * @var \Psr\Log\LoggerInterface
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
     * Run constructor.
     *
     * @param \BetaKiller\Daemon\DaemonFactory     $daemonFactory
     * @param \BetaKiller\Daemon\DaemonLockFactory $lockFactory
     * @param \BetaKiller\Helper\AppEnvInterface   $appEnv
     * @param \BetaKiller\Daemon\FsWatcher         $fsWatcher
     * @param \Psr\Log\LoggerInterface             $logger
     */
    public function __construct(
        DaemonFactory $daemonFactory,
        DaemonLockFactory $lockFactory,
        AppEnvInterface $appEnv,
        FsWatcher $fsWatcher,
        LoggerInterface $logger
    ) {
        $this->daemonFactory = $daemonFactory;
        $this->lockFactory   = $lockFactory;
        $this->appEnv        = $appEnv;
        $this->fsWatcher     = $fsWatcher;
        $this->logger        = $logger;

        $this->loop = Factory::create();

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
        if ($this->lock->isAcquired()) {
            $this->logger->warning('Daemon ":name" is already running', [
                ':name' => $this->codename,
            ]);

            // It is not normal to have multiple instances
            exit(1);
        }

        if (!$this->lock->acquire(\getmypid())) {
            throw new TaskException('Can not acquire lock for daemon ":name"', [
                ':name' => $this->codename,
            ]);
        }

        if (\gc_enabled()) {
            // Force GC to be called periodically
            // @see https://github.com/ratchetphp/Ratchet/issues/662
            $this->loop->addPeriodicTimer(60, static function () {
                gc_collect_cycles();
            });
        } else {
            $this->logger->warning('GC disabled but it is required for proper daemons processing');
        }

        $this->addSignalHandlers();

        $this->pingDB();

        $this->start();

        if ($this->appEnv->inDevelopmentMode()) {
            $this->startFsWatcher($this->loop);
        }

        $this->startMemoryConsumptionGuard();

        // Endless loop waiting for signals or exit()
        $this->loop->run();

        // Normal shutdown
        $this->shutdown(0);
    }

    private function start(): void
    {
        $this->setStatus(self::STATUS_STARTING);

        try {
            $this->daemon = $this->daemonFactory->create($this->codename);

            $this->daemon->startDaemon($this->loop);

            $this->logger->debug('Daemon ":name" is started', [
                ':name' => $this->codename,
            ]);
        } catch (\Throwable $e) {
            $this->processException($e);
        }

        $this->setStatus(self::STATUS_RUNNING);
    }

    private function stop(): void
    {
        // Prevent sequential stop calls
        if ($this->isStopping()) {
            $this->logger->notice('Daemon ":name" is already stopping, please wait', [
                ':name' => $this->codename,
            ]);

            return;
        }

        $this->setStatus(self::STATUS_STOPPING);

        // Wait 5 seconds for daemon stop
        $timeoutTimer = $this->loop->addTimer(self::STOP_TIMEOUT, function () {
            $this->logger->alert('Daemon ":name" had not stopped, force exit applied', [
                ':name' => $this->codename,
            ]);

            // Timeout => force kill + notify supervisor about problem via non-zero exit status
            $this->shutdown(1);
        });

        $this->loop->addPeriodicTimer(0.5, function(TimerInterface $pollTimer) use ($timeoutTimer) {
            if (!$this->daemon->isIdle()) {
                return;
            }

            $this->loop->cancelTimer($pollTimer);

            try {
                $this->daemon->stopDaemon($this->loop);

                if ($this->memoryConsumptionTimer) {
                    $this->loop->cancelTimer($this->memoryConsumptionTimer);
                }

                $this->logger->debug('Daemon ":name" was stopped', [
                    ':name' => $this->codename,
                ]);
            } catch (\Throwable $e) {
                $this->processException($e);
            } finally {
                $this->loop->cancelTimer($timeoutTimer);
            }

            // Simply exit with OK status and daemon would be restarted by supervisor
            $this->shutdown(0);
        });
    }

    private function shutdown(int $exitCode): void
    {
        $this->unlock();

        // Stop FS watcher if enabled
        $this->fsWatcher->stop();

        $this->logger->debug('Daemon ":name" has exited with exit code :code', [
            ':name' => $this->codename,
            ':code' => $exitCode,
        ]);

        exit($exitCode);
    }

    private function processException(\Throwable $e): void
    {
        if ($e instanceof ShutdownDaemonException) {
            // Normal shutdown
            $this->shutdown(0);
        } else {
            // Something wrong is going on here
            LoggerHelper::logRawException($this->logger, $e);
            $this->shutdown(1);
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

            $this->stop();
        };

        /**
         * @see https://stackoverflow.com/a/38991496
         */
        $this->loop->addSignal(\SIGHUP, $signalCallable);
        $this->loop->addSignal(\SIGINT, $signalCallable);
        $this->loop->addSignal(\SIGQUIT, $signalCallable);
        $this->loop->addSignal(\SIGTERM, $signalCallable);
    }

    private function pingDB(): void
    {
        $this->loop->addPeriodicTimer(60, static function () {
            Database::pingAll();
        });
    }

    private function startMemoryConsumptionGuard(): void
    {
        $usageOnStart = \memory_get_usage(true);

        $this->memoryConsumptionTimer = $this->loop->addPeriodicTimer(0.5, function () use ($usageOnStart) {
            // Prevent duplicate calls
            if (!$this->isRunning()) {
                return;
            }

            $isMemoryLeaking = \memory_get_usage(true) > $usageOnStart * self::MAX_MEMORY_RATIO;

            if ($isMemoryLeaking && $this->daemon->isIdle()) {
                $this->logger->notice('Daemon ":name" consumes too much memory, restarting', [
                    ':name' => $this->codename,
                ]);

                $this->stop();
            }
        });
    }

    private function unlock(): void
    {
        try {
            if ($this->lock->release()) {
                $this->logger->debug('Daemon ":name" was unlocked', [
                    ':name' => $this->codename,
                ]);
            } else {
                $this->logger->debug('Daemon ":name" is not locked', [
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
            $this->stop();
        });
    }
}
