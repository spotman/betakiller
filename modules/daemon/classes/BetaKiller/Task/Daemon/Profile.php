<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Console\ConsoleInputInterface;
use BetaKiller\ProcessLock\LockInterface;
use Symfony\Component\Process\Process;

final class Profile extends AbstractDaemonCommandTask
{
    protected function proceedCommand(string $daemonName, LockInterface $lock, ConsoleInputInterface $params): void
    {
        $this->checkLockExists($daemonName, $lock);

        // Get PID
        $pid = $lock->getPid();

        // Send signal to a process
        $process = new Process(['kill', '-s', Runner::SIGNAL_PROFILE, $pid]);
        $process->mustRun();
    }
}
