<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Notification\TransportInterface;

abstract class AbstractTransport implements TransportInterface
{
    protected function renderMessage(\Notification_Message $message)
    {
        return $message->render($this);
    }
}
