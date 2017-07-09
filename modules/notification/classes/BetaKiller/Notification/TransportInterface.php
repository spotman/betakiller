<?php
namespace BetaKiller\Notification;

interface TransportInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param \BetaKiller\Notification\NotificationUserInterface $user
     *
     * @return bool
     */
    public function isEnabledFor(NotificationUserInterface $user): bool;

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     * @param \BetaKiller\Notification\NotificationUserInterface    $user
     *
     * @return int Number of messages sent
     */
    public function send(NotificationMessageInterface $message, NotificationUserInterface $user): int;
}
