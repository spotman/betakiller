<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

final class DebugStopDelayDaemon extends AbstractDaemon
{
    public const CODENAME = 'DebugStopDelay';

    public function startDaemon(LoopInterface $loop): PromiseInterface
    {
        // Test start
        echo 'Starting DebugStopDelay daemon...';
        sleep(2);
        echo 'OK'.\PHP_EOL;

        return resolve();
    }

    public function stopDaemon(LoopInterface $loop): PromiseInterface
    {
        // Test stop
        echo 'Delaying daemon stop (timeout guard check)';
        sleep(AbstractDaemon::SHUTDOWN_TIMEOUT * 2);
        echo 'OK'.\PHP_EOL;

        return resolve();
    }
}
