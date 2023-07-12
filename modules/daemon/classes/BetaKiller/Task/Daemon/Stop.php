<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon;

use BetaKiller\Daemon\AbstractDaemon;
use BetaKiller\ProcessLock\LockInterface;
use BetaKiller\Task\TaskException;
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

        // Wait for daemon to be stopped
        $isFree = $lock->waitForRelease(AbstractDaemon::SHUTDOWN_TIMEOUT + 3);

        if (!$isFree) {
            throw new TaskException('Daemon ":name" was not stopped properly', [
                ':name' => $daemonName,
            ]);
        }
    }
}
