<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Task\TaskException;
use React\EventLoop\LoopInterface;

class FailingDaemon implements DaemonInterface
{
    public const CODENAME = 'Failing';

    public function start(LoopInterface $loop): void
    {
        // Test start
        echo 'Starting Failing daemon...';
        sleep(2);
        echo 'Failed!'.\PHP_EOL;

        throw new TaskException('Failing daemon was obviously failed');
    }

    public function stop(): void
    {
        // Test stop
        echo 'Stopping Sleep daemon...';
        sleep(1);
        echo 'OK'.\PHP_EOL;
    }
}
