<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

interface RestrictionInterface
{
    /**
     * @param \BetaKiller\MessageBus\RestrictionTargetInterface $target
     *
     * @return bool
     */
    public function isSatisfiedBy(RestrictionTargetInterface $target): bool;
}
