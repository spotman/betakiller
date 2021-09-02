<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Event;

use BetaKiller\Event\UserEmailConfirmedEvent;
use BetaKiller\MessageBus\EventMessageInterface;
use BetaKiller\Model\UserInterface;

final class UserEmailConfirmed extends AbstractUserEventTask
{
    protected function makeEvent(UserInterface $user): EventMessageInterface
    {
        return new UserEmailConfirmedEvent($user);
    }
}
