<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

final class DebugSleepAndRestartDaemon extends AbstractDaemon
{
    public const CODENAME = 'DebugSleepAndRestart';

    public function startDaemon(LoopInterface $loop): PromiseInterface
    {
        // Test start
        echo 'Starting Sleep daemon...';
        sleep(5);
        echo 'OK'.\PHP_EOL;

        $loop->addTimer(1, static function() {
            // Simulate normal restart
            throw new ShutdownDaemonException;
        });

        return resolve();
    }

    public function stopDaemon(LoopInterface $loop): PromiseInterface
    {
        // Test stop
        echo 'Stopping Sleep daemon...';
        usleep(500000);
        echo 'OK'.\PHP_EOL;

        return resolve();
    }
}
