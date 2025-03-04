<?php

declare(strict_types=1);

namespace BetaKiller\Notification;

use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Model\UserInterface;

final readonly class UserMessageTargetResolver implements MessageTargetResolverInterface
{
    public function __construct(private AppEnvInterface $appEnv)
    {
    }

    public function isDirectSendingAllowed(MessageTargetInterface $target): bool
    {
        // Always message directly in prod env
        if ($this->appEnv->inProductionMode()) {
            return true;
        }

        // Always message directly Admin Users
        if (($target instanceof UserInterface) && $target->isAdmin()) {
            return true;
        }

        // Redirect all messages while in debug mode (exclude production)
        return !$this->appEnv->isDebugEnabled();
    }
}
