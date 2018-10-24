<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use React\EventLoop\LoopInterface;

interface DaemonInterface
{
    public const NAMESPACES     = ['Daemon'];
    public const SUFFIX         = 'Daemon';
    public const RESTART_SIGNAL = SIGUSR1;

    public function start(LoopInterface $loop): void;

    public function stop(): void;

    public function restart(): void;
}
