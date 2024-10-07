<?php

declare(strict_types=1);

namespace BetaKiller\Task\Daemon\Supervisor;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Daemon\DaemonLockFactory;
use BetaKiller\Daemon\SupervisorDaemon;
use BetaKiller\Task\AbstractTask;
use Symfony\Component\Process\Process;

abstract class AbstractSupervisorSignalTask extends AbstractTask
{
    /**
     * @var \BetaKiller\Daemon\DaemonLockFactory
     */
    private DaemonLockFactory $lockFactory;

    /**
     * AbstractSupervisorSignalTask constructor.
     *
     * @param \BetaKiller\Daemon\DaemonLockFactory $lockFactory
     */
    public function __construct(DaemonLockFactory $lockFactory)
    {
        $this->lockFactory = $lockFactory;
    }

    /**
     * @inheritDoc
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array
    {
        return [];
    }

    public function run(ConsoleInputInterface $params): void
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
        $process = new Process(['kill', '-s', $this->getSignal(), $pid]);
        $process->mustRun();
    }

    abstract protected function getSignal(): int;
}
