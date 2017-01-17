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

    public function isEnabledFor(\Notification_User_Interface $user)
    {
        return $this->isOnline($user) && $user->is_online_notification_allowed();
    }

    /**
     * Returns TRUE if user is using the site now (so online notifications may be provided)
     *
     * @param \Notification_User_Interface $user
     *
     * @return bool
     */
    public function isOnline(\Notification_User_Interface $user)
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
     * @param \Notification_Message        $message
     * @param \Notification_User_Interface $user
     *
     * @return int Number of messages sent
     * @throws \HTTP_Exception_501
     */
    public function send(\Notification_Message $message, \Notification_User_Interface $user)
    {
        throw new \HTTP_Exception_501();
    }
}
