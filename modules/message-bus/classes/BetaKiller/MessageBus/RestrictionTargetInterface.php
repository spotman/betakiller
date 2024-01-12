<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

/**
 * Marker for EventBus Message Target
 */
interface RestrictionTargetInterface
{
    public function equalsTo(RestrictionTargetInterface $target): bool;
}
