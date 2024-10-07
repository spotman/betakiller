<?php

declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\ProcessLock\LockInterface;

class Status extends AbstractDaemonCommandTask
{
    protected function proceedCommand(string $daemonName, LockInterface $lock, ConsoleInputInterface $params): void
    {
        if ($lock->isAcquired() && $lock->isValid()) {
            echo sprintf('Daemon is running on pid %s'.PHP_EOL, $lock->getPid());
        } elseif ($lock->isAcquired()) {
            echo sprintf('Lock is acquired but daemon is stale on pid %s'.PHP_EOL, $lock->getPid());
        } else {
            echo 'Daemon is stopped'.PHP_EOL;
        }
    }
}
