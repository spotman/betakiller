<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\ProcessLock\LockInterface;
use Symfony\Component\Process\Process;

final class Stop extends AbstractDaemonCommandTask
{
    protected function proceedCommand(string $daemonName, LockInterface $lock): void
    {
        $this->checkLockExists($daemonName, $lock);

        // Get PID
        $pid = $lock->getPid();

        // Send signal to a process
        $process = new Process(['kill', '-s', \SIGTERM, $pid]);
        $process->run();
    }
}
