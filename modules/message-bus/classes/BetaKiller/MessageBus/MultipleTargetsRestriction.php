<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

final class MultipleTargetsRestriction implements RestrictionInterface
{
    /**
     * @var \BetaKiller\MessageBus\RestrictionTargetInterface[]
     */
    private array $targets;

    /**
     * @param \BetaKiller\MessageBus\RestrictionTargetInterface[] $targets
     */
    public function __construct(array $targets)
    {
        $this->targets = $targets;
    }

    public function isSatisfiedBy(RestrictionTargetInterface $target): bool
    {
        foreach ($this->targets as $item) {
            if ($item->equalsTo($target)) {
                return true;
            }
        }

        return false;
    }
}
