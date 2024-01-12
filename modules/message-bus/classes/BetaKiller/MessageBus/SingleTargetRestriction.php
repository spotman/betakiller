<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

final class SingleTargetRestriction implements RestrictionInterface
{
    /**
     * @var \BetaKiller\MessageBus\RestrictionTargetInterface
     */
    private RestrictionTargetInterface $target;

    public function __construct(RestrictionTargetInterface $target)
    {
        $this->target = $target;
    }

    public function isSatisfiedBy(RestrictionTargetInterface $target): bool
    {
        return $target->equalsTo($this->target);
    }
}
