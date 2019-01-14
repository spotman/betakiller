<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Notification\NotificationMessageInterface;
use BetaKiller\Notification\NotificationTargetInterface;

class EmailTransport extends AbstractTransport
{
    public function getName(): string
    {
        return 'email';
    }

    public function isEnabledFor(NotificationTargetInterface $user): bool
    {
        return $user->isEmailNotificationAllowed();
    }

    /**
     * Returns true if subject line is required for template rendering
     *
     * @return bool
     */
    public function isSubjectRequired(): bool
    {
        return true;
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     * @param \BetaKiller\Notification\NotificationTargetInterface  $target
     * @param string                                                $body
     *
     * @return bool Number of messages sent
     * @throws \BetaKiller\Exception
     */
    public function send(
        NotificationMessageInterface $message,
        NotificationTargetInterface $target,
        string $body
    ): bool {
        $fromUser = $message->getFrom();

        $from        = $fromUser ? $fromUser->getEmail() : null;
        $to          = $target->getEmail();
        $subj        = $message->getSubject();
        $attachments = $message->getAttachments();

        if (!$to) {
            throw new \InvalidArgumentException('Missing email target');
        }

        if (!$subj) {
            throw new \InvalidArgumentException('Missing email subject');
        }

        // Email notification
        return (bool)\Email::send($from, $to, $subj, $body, true, $attachments);
    }
}
