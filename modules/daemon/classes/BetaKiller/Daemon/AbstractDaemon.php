<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

abstract class AbstractDaemon implements DaemonInterface
{
    public function restart(): void
    {
        // Simply exit with OK status and daemon would be restarted by supervisor
        throw new RestartDaemonException;
    }
}
