<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon\Supervisor;

use BetaKiller\Daemon\DaemonLockFactory;
use BetaKiller\Daemon\SupervisorDaemon;
use BetaKiller\Task\AbstractTask;
use Symfony\Component\Process\Process;

class Reload extends AbstractTask
{
    /**
     * @var \BetaKiller\Daemon\DaemonLockFactory
     */
    private $lockFactory;

    /**
     * Reload constructor.
     *
     * @param \BetaKiller\Daemon\DaemonLockFactory $lockFactory
     */
    public function __construct(DaemonLockFactory $lockFactory)
    {
        $this->lockFactory = $lockFactory;

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
        return [];
    }

    public function run(): void
    {
        // Get lock
        $lock = $this->lockFactory->create(SupervisorDaemon::CODENAME);

        // Check lock file exists and points to a valid pid
        if (!$lock->isAcquired()) {
            echo 'Daemon is not running'.PHP_EOL;

            return;
        }

        // Get PID
        $pid = $lock->getPid();

        // Send signal to a process
        $process = new Process(['kill', '-s', SupervisorDaemon::RELOAD_SIGNAL, $pid]);
        $process->run();
    }
}
