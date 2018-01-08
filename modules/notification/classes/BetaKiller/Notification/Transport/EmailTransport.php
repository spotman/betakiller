<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Notification\MessageRendererInterface;
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
     * @param \BetaKiller\Notification\MessageRendererInterface     $renderer
     *
     * @return int Number of messages sent
     */
    public function send(
        NotificationMessageInterface $message,
        NotificationUserInterface $user,
        MessageRendererInterface $renderer
    ): int {
        $fromUser = $message->getFrom();

        $from        = $fromUser ? $fromUser->getEmail() : null;
        $to          = $user->getEmail();
        $subj        = $message->getSubj($user);
        $attachments = $message->getAttachments();

        $body = $renderer->render($message, $user, $this);

        // Email notification
        return \Email::send($from, $to, $subj, $body, true, $attachments);
    }
}
