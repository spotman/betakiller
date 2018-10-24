<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

class SleepDaemon implements DaemonInterface
{
    public const CODENAME = 'Sleep';

    public function start(): void
    {
        // Test start
        echo 'Starting Sleep daemon...';
        sleep(10);
        echo 'OK'.\PHP_EOL;
    }

    public function stop(): void
    {
        // Test stop
        echo 'Stopping Sleep daemon...';
        sleep(2);
        echo 'OK'.\PHP_EOL;
    }
}
