<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Notification\NotificationMessageInterface;
use BetaKiller\Notification\NotificationUserInterface;

class OnlineTransport extends AbstractTransport
{
    const NAME = 'email';

    public function getName(): string
    {
        return self::NAME;
    }

    public function isEnabledFor(NotificationUserInterface $user): bool
    {
        return $user->isOnlineNotificationAllowed() && $this->isOnline($user);
    }

    /**
     * Returns TRUE if user is using the site now (so online notifications may be provided)
     *
     * @param \BetaKiller\Notification\NotificationUserInterface $user
     *
     * @return bool
     */
    public function isOnline(NotificationUserInterface $user): bool
    {
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
    public function send(NotificationMessageInterface $message, NotificationUserInterface $user): int
    {
        throw new \HTTP_Exception_501('Not implemented yet');
    }
}
