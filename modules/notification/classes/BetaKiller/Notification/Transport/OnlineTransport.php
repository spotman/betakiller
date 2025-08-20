<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Exception\LogicException;
use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Notification\EnvelopeInterface;
use BetaKiller\Notification\Message\MessageInterface;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\OnlineMessageTargetInterface;

final readonly class OnlineTransport extends AbstractTransport
{
    public static function getName(): string
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

    public function isEnabledFor(MessageTargetInterface $target): bool
    {
        if (!$target instanceof OnlineMessageTargetInterface) {
            throw new LogicException('Message target must implement :class', [
                ':class' => OnlineMessageTargetInterface::class,
            ]);
        }

        return $this->isOnline($target) && $target->isOnlineNotificationAllowed();
    }

    /**
     * Returns true if current transport can handle provided message
     *
     * @param \BetaKiller\Notification\EnvelopeInterface $envelope
     *
     * @return bool
     */
    public function canHandle(EnvelopeInterface $envelope): bool
    {
        // Temporary disabled
        return false;
    }

    /**
     * @param \BetaKiller\Notification\Message\MessageInterface $message
     * @param \BetaKiller\Notification\MessageTargetInterface   $target
     * @param string                                            $body
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
