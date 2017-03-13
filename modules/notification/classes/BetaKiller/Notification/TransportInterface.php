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
    public function isEnabledFor(NotificationUserInterface $user);

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     * @param \BetaKiller\Notification\NotificationUserInterface    $user
     *
     * @return int Number of messages sent
     */
    public function send(NotificationMessageInterface $message, NotificationUserInterface $user);
}
