<?php
namespace BetaKiller\Notification;

interface TransportInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param \BetaKiller\Notification\MessageTargetInterface $user
     *
     * @return bool
     */
    public function isEnabledFor(MessageTargetInterface $user): bool;

    /**
     * @param \BetaKiller\Notification\MessageInterface       $message
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     * @param string                                          $body
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
