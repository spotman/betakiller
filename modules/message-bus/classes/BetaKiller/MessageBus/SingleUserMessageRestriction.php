<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

use BetaKiller\Model\UserInterface;

final class SingleUserMessageRestriction implements MessageRestrictionInterface
{
    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private UserInterface $target;

    public function __construct(UserInterface $target)
    {
        $this->target = $target;
    }

    public function isSatisfiedBy(UserInterface $user): bool
    {
        return $user->isEqualTo($this->target);
    }
}
