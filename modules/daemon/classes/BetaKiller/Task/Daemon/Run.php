<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Daemon\DaemonFactory;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Task\AbstractTask;

class Run extends AbstractTask
{
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
     * Run constructor.
     *
     * @param \BetaKiller\Daemon\DaemonFactory   $factory
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     */
    public function __construct(DaemonFactory $factory, AppEnvInterface $appEnv)
    {
        $this->appEnv  = $appEnv;
        $this->factory = $factory;

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

        // Check if it is running already and exit if so
        if (!$this->tryLock()) {
            echo sprintf('Daemon "%s" is already running'.\PHP_EOL, $this->codename);

            return;
        }

        $this->addSignalHandlers();

        $this->daemon = $this->factory->create($this->codename);
        $this->daemon->start();
    }

    public function stop(): void
    {
        $this->daemon->stop();
        $this->unlock();
    }

    private function addSignalHandlers(): void
    {
        // Remove the lock on exit (Control+C doesn't count as 'exit')
        register_shutdown_function([$this, 'stop']);

        $stopCallable = function () {
            $this->stop();
            exit(0);
        };

        \pcntl_signal(\SIGHUP, $stopCallable);
        \pcntl_signal(\SIGQUIT, $stopCallable);
        \pcntl_signal(\SIGTERM, $stopCallable);
        \pcntl_signal(\SIGINT, $stopCallable);
    }

    private function unlock(): void
    {
        $file = $this->getLockFileName();

        if (\file_exists($file)) {
            \unlink($file);
        }
    }

    private function tryLock(): bool
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
            return $this->tryLock();
        }

        return false;
    }

    private function getLockFileName(): string
    {
        return $this->appEnv->getTempPath().\DIRECTORY_SEPARATOR.'.'.$this->codename.'.lock';
    }
}
