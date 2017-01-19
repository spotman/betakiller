<?php
namespace BetaKiller\Notification;


interface TransportInterface
{
    /**
     * @return string
     */
    public function get_name();

    /**
     * @param \BetaKiller\Notification\NotificationUserInterface $user
     *
     * @return bool
     */
    public function isEnabledFor(\BetaKiller\Notification\NotificationUserInterface $user);

    /**
     * @param \Notification_Message                              $message
     * @param \BetaKiller\Notification\NotificationUserInterface $user
     *
     * @return int Number of messages sent
     */
    public function send(\Notification_Message $message, \BetaKiller\Notification\NotificationUserInterface $user);
}
