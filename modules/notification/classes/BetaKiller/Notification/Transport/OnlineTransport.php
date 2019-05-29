<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Notification\MessageInterface;
use BetaKiller\Notification\TargetInterface;

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

    public function isEnabledFor(TargetInterface $user): bool
    {
        return $user->isOnlineNotificationAllowed() && $this->isOnline($user);
    }

    /**
     * Returns TRUE if user is using the site now (so online notifications may be provided)
     *
     * @param \BetaKiller\Notification\TargetInterface $user
     *
     * @return bool
     */
    public function isOnline(TargetInterface $user): bool
    {
        // TODO Online detection logic
        // Check websocket connection

        return !$user;
    }

    /**
     * @param \BetaKiller\Notification\MessageInterface $message
     * @param \BetaKiller\Notification\TargetInterface  $target
     * @param string                                    $body
     *
     * @return bool Number of messages sent
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function send(
        MessageInterface $message,
        TargetInterface $target,
        string $body
    ): bool {
        throw new NotImplementedHttpException();
    }
}
