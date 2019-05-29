<?php
namespace BetaKiller\Notification;

interface TransportInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param \BetaKiller\Notification\TargetInterface $user
     *
     * @return bool
     */
    public function isEnabledFor(TargetInterface $user): bool;

    /**
     * @param \BetaKiller\Notification\MessageInterface $message
     * @param \BetaKiller\Notification\TargetInterface  $target
     * @param string                                    $body
     *
     * @return bool Number of messages sent
     */
    public function send(
        MessageInterface $message,
        TargetInterface $target,
        string $body
    ): bool;

    /**
     * Returns true if subject line is required for template rendering
     *
     * @return bool
     */
    public function isSubjectRequired(): bool;
}
