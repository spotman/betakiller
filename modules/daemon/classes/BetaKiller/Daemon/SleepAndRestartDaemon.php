<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use React\EventLoop\LoopInterface;

class SleepAndRestartDaemon implements DaemonInterface
{
    public const CODENAME = 'SleepAndRestart';

    public function start(LoopInterface $loop): void
    {
        // Test start
        echo 'Starting Sleep daemon...';
        sleep(5);
        echo 'OK'.\PHP_EOL;

        // Simulate normal restart
        throw new ShutdownDaemonException;
    }

    public function stop(): void
    {
        // Test stop
        echo 'Stopping Sleep daemon...';
        usleep(500000);
        echo 'OK'.\PHP_EOL;
    }
}
