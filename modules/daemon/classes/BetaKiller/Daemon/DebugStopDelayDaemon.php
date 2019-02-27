<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Task\Daemon\Runner;
use React\EventLoop\LoopInterface;

class DebugStopDelayDaemon implements DaemonInterface
{
    public const CODENAME = 'DebugStopDelay';

    public function start(LoopInterface $loop): void
    {
        // Test start
        echo 'Starting DebugStopDelay daemon...';
        sleep(2);
        echo 'OK'.\PHP_EOL;
    }

    public function stop(): void
    {
        // Test stop
        echo 'Delaying daemon stop (timeout guard check)';
        sleep(Runner::STOP_TIMEOUT * 2);
        echo 'OK'.\PHP_EOL;
    }
}
