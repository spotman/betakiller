<?php

declare(strict_types=1);

namespace BetaKiller\Notification;

interface MessageTargetResolverInterface
{
    public function isDirectSendingAllowed(MessageTargetInterface $target): bool;
}
