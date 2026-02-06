<?php

declare(strict_types=1);

namespace BetaKiller\MessageBus;

final class AnyTargetRestriction implements RestrictionInterface
{
    public function isSatisfiedBy(RestrictionTargetInterface $target): bool
    {
        return true;
    }
}
