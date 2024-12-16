<?php

declare(strict_types=1);

namespace BetaKiller\Api\Method\UserNotification;

use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\UserInterface;

readonly class EnableGroupApiMethod extends AbstractUpdateGroupApiMethod
{
    protected function processGroup(NotificationGroupInterface $group, UserInterface $user): void
    {
        if (!$group->isEnabledForUser($user)) {
            $group->enableForUser($user);
        }
    }
}
