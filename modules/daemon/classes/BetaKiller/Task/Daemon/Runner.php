<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Daemon\DaemonFactory;
use BetaKiller\Daemon\LockFactory;
use BetaKiller\Daemon\ShutdownDaemonException;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use Database;
use Psr\Log\LoggerInterface;
use React\EventLoop\Factory;

class Runner extends AbstractTask
{
    public const START_TIMEOUT = 5;
    public const STOP_TIMEOUT  = 15;

    private const STATUS_STARTING = 'starting';
    private const STATUS_STARTED  = 'started';
    private const STATUS_STOPPING = 'stopping';

    /**
     * @var \BetaKiller\Daemon\DaemonFactory
     */
    private $daemonFactory;

    /**
     * @var \BetaKiller\Daemon\LockFactory
     */
    private $lockFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $codename;

    /**
     * @var \BetaKiller\Daemon\DaemonInterface
     */
    private $daemon;

    /**
     * @var \React\EventLoop\LoopInterface
     */
    private $loop;

    /**
     * @var \BetaKiller\Daemon\Lock
     */
    private $lock;

    /**
     * @var string
     */
    private $status;

    /**
     * Run constructor.
     *
     * @param \BetaKiller\Daemon\DaemonFactory $daemonFactory
     * @param \BetaKiller\Daemon\LockFactory   $lockFactory
     * @param \Psr\Log\LoggerInterface         $logger
     */
    public function __construct(
        DaemonFactory $daemonFactory,
        LockFactory $lockFactory,
        LoggerInterface $logger
    ) {
        $this->daemonFactory = $daemonFactory;
        $this->logger        = $logger;
        $this->lockFactory   = $lockFactory;

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

        $this->loop = Factory::create();

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

        $this->setStatus(self::STATUS_STARTED);
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

        try {
            $this->daemon->stopDaemon($this->loop);

            $this->loop->cancelTimer($timeoutTimer);

            $this->logger->debug('Daemon ":name" was stopped', [
                ':name' => $this->codename,
            ]);
        } catch (\Throwable $e) {
            $this->loop->cancelTimer($timeoutTimer);

            $this->processException($e);
        }

        // Simply exit with OK status and daemon would be restarted by supervisor
        $this->shutdown(0);
    }

    private function shutdown(int $exitCode): void
    {
        $this->unlock();

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

    private function isStarted(): bool
    {
        return $this->status === self::STATUS_STARTED;
    }

    private function isStopping(): bool
    {
        return $this->status === self::STATUS_STOPPING;
    }
}
