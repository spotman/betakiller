<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Daemon\DaemonFactory;
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
     * @var \BetaKiller\Daemon\DaemonInterface
     */
    private $daemon;

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
            'name' => null,
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
            echo sprintf('Daemon "%s" is already running'.\PHP_EOL, $this->codename);

            return;
        }

        $this->addSignalHandlers();

        $this->daemon = $this->factory->create($this->codename);

        try {
            $this->daemon->start();
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);
            $this->shutdown();
            exit(1);
        }
    }

    public function shutdown(): void
    {
        $this->daemon->stop();

        $this->unlock();
    }

    private function addSignalHandlers(): void
    {
        pcntl_async_signals(true);

        $signalCallable = function (int $signal) {
            $this->logger->debug('Received ":value" signal for ":name" daemon', [
                ':value' => $signal,
                ':name'  => $this->codename,
            ]);
            $this->shutdown();
            exit(0);
        };

        /**
         * @see https://stackoverflow.com/a/38991496
         */
        \pcntl_signal(\SIGHUP, $signalCallable);
        \pcntl_signal(\SIGINT, $signalCallable);
        \pcntl_signal(\SIGQUIT, $signalCallable);
        \pcntl_signal(\SIGTERM, $signalCallable);
        \pcntl_signal(\SIGALRM, $signalCallable);
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
