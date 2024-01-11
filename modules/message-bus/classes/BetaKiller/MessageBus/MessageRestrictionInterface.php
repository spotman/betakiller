<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

use BetaKiller\Model\UserInterface;

interface MessageRestrictionInterface
{
    public function isSatisfiedBy(UserInterface $user): bool;
}
