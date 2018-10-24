<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Daemon\DaemonFactory;
use BetaKiller\Daemon\DaemonInterface;
use BetaKiller\Daemon\RestartDaemonException;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class Run extends AbstractTask
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Daemon\DaemonFactory
     */
    private $factory;

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var string
     */
    private $codename;

    /**
     * @var
     */
    private $isStarted;

    /**
     * @var \BetaKiller\Daemon\DaemonInterface
     */
    private $daemon;

    /**
     * @var \React\EventLoop\LoopInterface
     */
    private $loop;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Run constructor.
     *
     * @param \BetaKiller\Daemon\DaemonFactory   $factory
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     * @param \Psr\Log\LoggerInterface           $logger
     */
    public function __construct(DaemonFactory $factory, AppEnvInterface $appEnv, LoggerInterface $logger)
    {
        $this->appEnv  = $appEnv;
        $this->factory = $factory;
        $this->logger  = $logger;

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
            'name'    => null,
            'restart' => 'false',
        ];
    }

    public function run(): void
    {
        $this->codename = \ucfirst((string)$this->getOption('name', true));

        if (!$this->codename) {
            throw new \LogicException('Daemon codename is not defined');
        }

        // Check if it is running already and exit if so
        if (!$this->lock()) {
            $this->logger->debug('Daemon ":name" is already running', [
                ':name' => $this->codename,
            ]);

            return;
        }

        $this->loop = \React\EventLoop\Factory::create();

        $this->addSignalHandlers();

        $this->daemon = $this->factory->create($this->codename);

        $this->start();

        // Endless loop waiting for signals or exit()
        $this->loop->run();
    }

    private function start(): void
    {
        $this->wrap(function () {
            $this->daemon->start($this->loop);
            $this->isStarted = true;
            $this->logger->debug('Daemon ":name" was started', [
                ':name' => $this->codename,
            ]);
        });
    }

    private function stop(): void
    {
        $this->wrap(function () {
            $this->daemon->stop();
            $this->isStarted = false;

            $this->logger->debug('Daemon ":name" was stopped', [
                ':name' => $this->codename,
            ]);
        });
    }

    private function restart(): void
    {
        if ($this->isStarted) {
            $this->stop();
        }
        $this->wrap(function () {
            $this->isStarted = true;
            $this->daemon->restart();
        });
    }

    private function shutdown(int $exitCode): void
    {
        $this->stop();
        $this->unlock();

        $this->logger->debug('Shutting down daemon ":name" with exit code [:code]', [
            ':name' => $this->codename,
            ':code' => $exitCode,
        ]);

        exit($exitCode);
    }

    private function wrap(callable $func): void
    {
        try {
            $func();
        } catch (RestartDaemonException $e) {
            $this->shutdown(0);
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);
            $this->shutdown(1);
        }
    }

    private function addSignalHandlers(): void
    {
        pcntl_async_signals(true);

        $signalCallable = function (int $signal) {
            $this->logger->debug('Received signal ":value" for ":name" daemon', [
                ':value' => $signal,
                ':name'  => $this->codename,
            ]);
            $this->shutdown(0);
        };

        // Restart
        $this->loop->addSignal(DaemonInterface::RESTART_SIGNAL, function () {
            $this->logger->debug('Restarting daemon ":name"', [
                ':name' => $this->codename,
            ]);
            $this->restart();
        });

        /**
         * @see https://stackoverflow.com/a/38991496
         */
        $this->loop->addSignal(\SIGHUP, $signalCallable);
        $this->loop->addSignal(\SIGINT, $signalCallable);
        $this->loop->addSignal(\SIGQUIT, $signalCallable);
        $this->loop->addSignal(\SIGTERM, $signalCallable);
    }

    private function lock(): bool
    {
        $lockFile = $this->getLockFileName();

        // If lock file exists, check if stale.  If exists and is not stale, return TRUE
        // Else, create lock file and return FALSE.

        // The @ in front of 'symlink' is to suppress the NOTICE you get if the LOCK_FILE exists
        if (@symlink('/proc/'.getmypid(), $lockFile) !== false) {
            return true;
        }

        // Link already exists, check if it's stale
        if (is_link($lockFile) && !is_dir($lockFile)) {
            \unlink($lockFile);

            // Try to lock again
            return $this->lock();
        }

        return false;
    }

    private function unlock(): void
    {
        $file = $this->getLockFileName();

        if (\file_exists($file)) {
            \unlink($file);
            $this->logger->debug('Daemon ":name" was unlocked', [
                ':name' => $this->codename,
            ]);
        } else {
            $this->logger->debug('Daemon lock-file ":path" does not exists', [
                ':path' => $file,
            ]);
        }
    }

    private function getLockFileName(): string
    {
        if (!$this->codename) {
            throw new \LogicException('Daemon codename is not defined');
        }

        return $this->appEnv->getTempPath().\DIRECTORY_SEPARATOR.'.'.$this->codename.'.daemon.lock';
    }
}
