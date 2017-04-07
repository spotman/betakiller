<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Notification\NotificationMessageInterface;
use BetaKiller\Notification\NotificationUserInterface;
use BetaKiller\Notification\TransportInterface;

abstract class AbstractTransport implements TransportInterface
{
    protected function renderMessage(NotificationMessageInterface $message, NotificationUserInterface $user)
    {
        return $message->render($this, $user);
    }
}
