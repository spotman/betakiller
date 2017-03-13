<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Notification\NotificationMessageInterface;
use BetaKiller\Notification\TransportInterface;

abstract class AbstractTransport implements TransportInterface
{
    protected function renderMessage(NotificationMessageInterface $message)
    {
        return $message->render($this);
    }
}
