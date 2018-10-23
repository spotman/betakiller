<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

class SleepDaemon implements DaemonInterface
{
    public const CODENAME = 'Sleep';

    public function start(): void
    {
        // Test start
        sleep(10);
    }

    public function stop(): void
    {
        // Test stop
        sleep(2);
    }
}
