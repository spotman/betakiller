<?php
declare(strict_types=1);

namespace BetaKiller\Api\Method\UserNotification;

use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\UserInterface;

class DisableGroupApiMethod extends AbstractUpdateGroupApiMethod
{
    protected function processGroup(NotificationGroupInterface $group, UserInterface $user): void
    {
        if ($group->isEnabledForUser($user)) {
            $group->disableForUser($user);
        }
    }
}
