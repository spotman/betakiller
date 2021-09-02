<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test\Event;

use BetaKiller\Event\UserPhoneConfirmedEvent;
use BetaKiller\MessageBus\EventMessageInterface;
use BetaKiller\Model\UserInterface;

final class UserPhoneConfirmed extends AbstractUserEventTask
{
    protected function makeEvent(UserInterface $user): EventMessageInterface
    {
        return new UserPhoneConfirmedEvent($user);
    }
}
