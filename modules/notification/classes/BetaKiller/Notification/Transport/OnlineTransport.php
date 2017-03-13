<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Notification\NotificationMessageInterface;
use BetaKiller\Notification\NotificationUserInterface;
use BetaKiller\Notification\TransportInterface;
//use BetaKiller\Notification\TransportException;

class OnlineTransport extends AbstractTransport implements TransportInterface
{
    const NAME = 'email';

    public function get_name()
    {
        return self::NAME;
    }

    public function isEnabledFor(NotificationUserInterface $user)
    {
        return $this->isOnline($user) && $user->is_online_notification_allowed();
    }

    /**
     * Returns TRUE if user is using the site now (so online notifications may be provided)
     *
     * @param \BetaKiller\Notification\NotificationUserInterface $user
     *
     * @return bool
     */
    public function isOnline(NotificationUserInterface $user)
    {
        // Non-logged user is always offline
        if (!$user->get_id()) {
            return false;
        }

        // TODO Online detection logic
        // Check websocket connection

        return false;
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     * @param \BetaKiller\Notification\NotificationUserInterface    $user
     *
     * @return int Number of messages sent
     * @throws \HTTP_Exception_501
     */
    public function send(NotificationMessageInterface $message, NotificationUserInterface $user)
    {
        throw new \HTTP_Exception_501();
    }
}
