<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Daemon\DaemonLockFactory;
use BetaKiller\ProcessLock\LockInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use Symfony\Component\Process\Process;

abstract class AbstractDaemonCommandTask extends AbstractTask
{
    /**
     * @var \BetaKiller\Daemon\DaemonLockFactory
     */
    private DaemonLockFactory $lockFactory;

    /**
     * Stop constructor.
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
        return [
            'name' => null,
        ];
    }

    public function run(): void
    {
        $name = \ucfirst((string)$this->getOption('name', true));

        if (!$name) {
            throw new \LogicException('Daemon codename is not defined');
        }

        // Get lock
        $lock = $this->lockFactory->create($name);

        $this->proceedCommand($name, $lock);

        // Check lock file exists and points to a valid pid
        if (!$lock->isAcquired()) {
            echo sprintf('Daemon "%s" is not running'.PHP_EOL, $name);

            return;
        }

        // Check lock file exists and points to a valid pid
        if (!$lock->isValid()) {
            echo sprintf('Daemon "%s" is stale with pid %d, lock will be released'.PHP_EOL, $name, $lock->getPid());
            $lock->release();

            return;
        }

        // Get PID
        $pid = $lock->getPid();

        // Send signal to a process
        $process = new Process(['kill', '-s', Runner::SIGNAL_PROFILE, $pid]);
        $process->mustRun();
    }

    protected function checkLockExists(string $daemonName, LockInterface $lock): void
    {
        // Check lock file exists and points to a valid pid
        if (!$lock->isAcquired()) {
            throw new TaskException('Daemon ":name" is not running', [
                ':name' => $daemonName,
            ]);
        }

        // Check lock file exists and points to a valid pid
        if (!$lock->isValid()) {
            $lock->release();

            throw new TaskException('Daemon ":name" is stale with pid :pid, lock will be released', [
                ':name' => $daemonName,
                ':pid'  => $lock->getPid(),
            ]);
        }
    }

    abstract protected function proceedCommand(string $daemonName, LockInterface $lock): void;
}
