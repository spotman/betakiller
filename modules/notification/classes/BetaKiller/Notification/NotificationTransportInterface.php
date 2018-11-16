<?php
namespace BetaKiller\Notification;

interface NotificationTransportInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param \BetaKiller\Notification\NotificationTargetInterface $user
     *
     * @return bool
     */
    public function isEnabledFor(NotificationTargetInterface $user): bool;

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     * @param \BetaKiller\Notification\NotificationTargetInterface  $target
     * @param string                                                $body
     *
     * @return bool Number of messages sent
     */
    public function send(
        NotificationMessageInterface $message,
        NotificationTargetInterface $target,
        string $body
    ): bool;

    /**
     * Returns true if subject line is required for template rendering
     *
     * @return bool
     */
    public function isSubjectRequired(): bool;
}
