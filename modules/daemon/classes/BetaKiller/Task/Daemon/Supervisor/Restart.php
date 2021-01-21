<?php
declare(strict_types=1);

namespace BetaKiller\Task\Daemon\Supervisor;

use BetaKiller\Daemon\SupervisorDaemon;

final class Restart extends AbstractSupervisorSignalTask
{
    protected function getSignal(): int
    {
        return SupervisorDaemon::SIGNAL_RESTART;
    }
}
