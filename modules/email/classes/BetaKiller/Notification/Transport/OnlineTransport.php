<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Notification\TransportInterface;
//use BetaKiller\Notification\TransportException;

class OnlineTransport extends AbstractTransport implements TransportInterface
{
    const NAME = 'email';

    public function get_name()
    {
        return self::NAME;
    }

    public function isEnabledFor(\BetaKiller\Notification\NotificationUserInterface $user)
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
    public function isOnline(\BetaKiller\Notification\NotificationUserInterface $user)
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
     * @param \Notification_Message                              $message
     * @param \BetaKiller\Notification\NotificationUserInterface $user
     *
     * @return int Number of messages sent
     * @throws \HTTP_Exception_501
     */
    public function send(\Notification_Message $message, \BetaKiller\Notification\NotificationUserInterface $user)
    {
        throw new \HTTP_Exception_501();
    }
}
