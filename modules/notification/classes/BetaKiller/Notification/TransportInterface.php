<?php
namespace BetaKiller\Notification;

use BetaKiller\Notification\Message\MessageInterface;

interface TransportInterface
{
    /**
     * @return string
     */
    public static function getName(): string;

    /**
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     *
     * @return bool
     */
    public function isEnabledFor(MessageTargetInterface $target): bool;

    /**
     * Returns true if current transport can handle provided message
     *
     * @param \BetaKiller\Notification\EnvelopeInterface $envelope
     *
     * @return bool
     */
    public function canHandle(EnvelopeInterface $envelope): bool;

    /**
     * @param \BetaKiller\Notification\Message\MessageInterface $message
     * @param \BetaKiller\Notification\MessageTargetInterface   $target
     * @param string                                            $body
     *
     * @return bool Number of messages sent
     */
    public function send(
        MessageInterface $message,
        MessageTargetInterface $target,
        string $body
    ): bool;

    /**
     * Returns true if subject line is required for template rendering
     *
     * @return bool
     */
    public function isSubjectRequired(): bool;
}
