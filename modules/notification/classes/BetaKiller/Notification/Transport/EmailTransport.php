<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Notification\NotificationMessageInterface;
use BetaKiller\Notification\NotificationUserInterface;

class EmailTransport extends AbstractTransport
{
    public function getName(): string
    {
        return 'email';
    }

    public function isEnabledFor(NotificationUserInterface $user): bool
    {
        return $user->isEmailNotificationAllowed();
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     * @param \BetaKiller\Notification\NotificationUserInterface    $user
     *
     * @return int Number of messages sent
     */
    public function send(NotificationMessageInterface $message, NotificationUserInterface $user): int
    {
        $fromUser = $message->getFrom();

        $from        = $fromUser ? $fromUser->getEmail() : null;
        $to          = $user->getEmail();
        $subj        = $message->getSubj($user);
        $attachments = $message->getAttachments();

        $body = $this->renderMessage($message, $user);

        // Email notification
        return \Email::send($from, $to, $subj, $body, true, $attachments);
    }
}
