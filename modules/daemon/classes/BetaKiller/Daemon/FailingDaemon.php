<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Task\TaskException;

class FailingDaemon implements DaemonInterface
{
    public const CODENAME = 'Failing';

    public function start(): void
    {
        // Test start
        echo 'Starting Failing daemon...';
        sleep(3);
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
