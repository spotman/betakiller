<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use React\EventLoop\LoopInterface;

class SleepDaemon extends AbstractDaemon
{
    public const CODENAME = 'Sleep';

    public function start(LoopInterface $loop): void
    {
        // Test start
        echo 'Starting Sleep daemon...';
        sleep(10);
        echo 'OK'.\PHP_EOL;

        $this->restart();
    }

    public function stop(): void
    {
        // Test stop
        echo 'Stopping Sleep daemon...';
        usleep(500000);
        echo 'OK'.\PHP_EOL;
    }
}
