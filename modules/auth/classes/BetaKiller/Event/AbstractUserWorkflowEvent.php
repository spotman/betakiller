<?php
declare(strict_types=1);

namespace BetaKiller\Event;

use BetaKiller\MessageBus\EventMessageInterface;
use BetaKiller\Model\UserInterface;

abstract class AbstractUserWorkflowEvent implements EventMessageInterface
{
    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * AbstractUserWorkflowEvent constructor.
     *
     * @param \BetaKiller\Model\UserInterface $user
     */
    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return \BetaKiller\Model\UserInterface
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
