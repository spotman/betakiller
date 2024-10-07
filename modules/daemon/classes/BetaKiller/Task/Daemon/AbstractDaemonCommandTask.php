<?php

declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\Console\ConsoleOptionBuilderInterface;
use BetaKiller\Daemon\DaemonLockFactory;
use BetaKiller\ProcessLock\LockInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;

abstract class AbstractDaemonCommandTask extends AbstractTask
{
    private const ARG_NAME = 'name';

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

    public function run(ConsoleInputInterface $params): void
    {
        $name = \ucfirst($params->getString(self::ARG_NAME));

        if (!$name) {
            throw new \LogicException('Daemon codename is not defined');
        }

        // Get lock
        $lock = $this->lockFactory->create($name);

        $this->proceedCommand($name, $lock, $params);
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

    abstract protected function proceedCommand(string $daemonName, LockInterface $lock, ConsoleInputInterface $params): void;
}
