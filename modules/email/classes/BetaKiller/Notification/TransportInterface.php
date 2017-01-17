<?php
namespace BetaKiller\Notification;


interface TransportInterface
{
    /**
     * @return string
     */
    public function get_name();

    /**
     * @param \Notification_User_Interface $user
     *
     * @return bool
     */
    public function isEnabledFor(\Notification_User_Interface $user);

    /**
     * @param \Notification_Message        $message
     * @param \Notification_User_Interface $user
     *
     * @return int Number of messages sent
     */
    public function send(\Notification_Message $message, \Notification_User_Interface $user);
}
