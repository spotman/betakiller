<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use React\EventLoop\LoopInterface;

interface DaemonInterface
{
    public const NAMESPACES = ['Daemon'];
    public const SUFFIX     = 'Daemon';

    /**
     * @param \React\EventLoop\LoopInterface $loop
     */
    public function startDaemon(LoopInterface $loop): void;

    /**
     * @param \React\EventLoop\LoopInterface $loop
     */
    public function stopDaemon(LoopInterface $loop): void;
}
