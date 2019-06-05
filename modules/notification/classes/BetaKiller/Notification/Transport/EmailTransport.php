<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Notification\MessageInterface;
use BetaKiller\Notification\TargetInterface;

class EmailTransport extends AbstractTransport
{
    public function getName(): string
    {
        return 'email';
    }

    public function isEnabledFor(TargetInterface $user): bool
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
     * @param \BetaKiller\Notification\MessageInterface $message
     * @param \BetaKiller\Notification\TargetInterface  $target
     * @param string                                    $body
     *
     * @return bool Number of messages sent
     * @throws \BetaKiller\Exception
     */
    public function send(
        MessageInterface $message,
        TargetInterface $target,
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

        // Fake delay to prevent blackout of SMTP relay
        sleep(2);

        // Email notification
        return (bool)\Email::send($from, $to, $subj, $body, true, $attachments);
    }
}
