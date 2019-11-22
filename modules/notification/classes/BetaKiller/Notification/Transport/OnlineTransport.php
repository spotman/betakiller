<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Notification\MessageInterface;
use BetaKiller\Notification\MessageTargetInterface;

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

    public function isEnabledFor(MessageTargetInterface $user): bool
    {
        return $user->isOnlineNotificationAllowed() && $this->isOnline($user);
    }

    /**
     * @param \BetaKiller\Notification\MessageInterface       $message
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     * @param string                                          $body
     *
     * @return bool Number of messages sent
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function send(
        MessageInterface $message,
        MessageTargetInterface $target,
        string $body
    ): bool {
        throw new NotImplementedHttpException();
    }

    /**
     * Returns TRUE if user is using the site now (so online notifications may be provided)
     *
     * @param \BetaKiller\Notification\MessageTargetInterface $user
     *
     * @return bool
     */
    protected function isOnline(MessageTargetInterface $user): bool
    {
        // TODO Online detection logic
        // Check websocket connection

        return !$user;
    }
}
