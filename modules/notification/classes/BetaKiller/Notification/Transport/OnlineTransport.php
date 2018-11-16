<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Notification\NotificationMessageInterface;
use BetaKiller\Notification\NotificationTargetInterface;

class OnlineTransport extends AbstractTransport
{
    public function getName(): string
    {
        return 'online';
    }

    /**
     * Returns true if subject line is required for template rendering
     *
     * @return bool
     */
    public function isSubjectRequired(): bool
    {
        return false;
    }

    public function isEnabledFor(NotificationTargetInterface $user): bool
    {
        return $user->isOnlineNotificationAllowed() && $this->isOnline($user);
    }

    /**
     * Returns TRUE if user is using the site now (so online notifications may be provided)
     *
     * @param \BetaKiller\Notification\NotificationTargetInterface $user
     *
     * @return bool
     */
    public function isOnline(NotificationTargetInterface $user): bool
    {
        // TODO Online detection logic
        // Check websocket connection

        return !$user;
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     * @param \BetaKiller\Notification\NotificationTargetInterface  $target
     * @param string                                                $body
     *
     * @return bool Number of messages sent
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function send(
        NotificationMessageInterface $message,
        NotificationTargetInterface $target,
        string $body
    ): bool {
        throw new NotImplementedHttpException();
    }
}
