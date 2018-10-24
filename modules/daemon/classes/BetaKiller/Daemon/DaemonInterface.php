<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

interface DaemonInterface
{
    public const NAMESPACES = ['Daemon'];
    public const SUFFIX     = 'Daemon';

    public function start(): void;

    public function stop(): void;
}
