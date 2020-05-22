<?php
declare(strict_types=1);

namespace BetaKiller\Notification;

interface ScheduleTargetSpecInterface
{
    /**
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     *
     * @return bool
     */
    public function isAllowedTo(MessageTargetInterface $target): bool;
}
