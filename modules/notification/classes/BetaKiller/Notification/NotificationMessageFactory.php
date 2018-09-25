<?php
namespace BetaKiller\Notification;

class NotificationMessageFactory
{
    /**
     * @param string $messageCodename
     *
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function create(string $messageCodename): NotificationMessageInterface
    {
        return new NotificationMessage($messageCodename);
    }
}
