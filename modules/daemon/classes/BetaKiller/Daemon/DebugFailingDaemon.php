<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Task\TaskException;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

final class DebugFailingDaemon extends AbstractDaemon
{
    public const CODENAME = 'DebugFailing';

    public function startDaemon(LoopInterface $loop): PromiseInterface
    {
        // Test start
        echo 'Starting DebugFailing daemon...';
        sleep(2);
        echo 'OK!'.\PHP_EOL;

        throw new TaskException('Failing daemon was obviously failed');
    }

    public function stopDaemon(LoopInterface $loop): PromiseInterface
    {
        // Test stop
        echo 'Stopping DebugFailing daemon...';
        sleep(1);
        echo 'OK'.\PHP_EOL;

        return resolve();
    }
}
