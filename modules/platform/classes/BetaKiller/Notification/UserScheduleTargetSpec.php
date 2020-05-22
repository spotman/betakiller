<?php
declare(strict_types=1);

namespace BetaKiller\Notification;

use BetaKiller\Model\UserInterface;

final class UserScheduleTargetSpec implements ScheduleTargetSpecInterface
{
    /**
     * @inheritDoc
     */
    public function isAllowedTo(MessageTargetInterface $target): bool
    {
        if (!$target instanceof UserInterface) {
            throw new \LogicException();
        }

        return $target->isActive();
    }
}
