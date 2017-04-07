<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Notification\NotificationMessageInterface;
use BetaKiller\Notification\NotificationUserInterface;

class EmailTransport extends AbstractTransport
{
    public function get_name()
    {
        return 'email';
    }

    public function isEnabledFor(NotificationUserInterface $user)
    {
        return $user->is_email_notification_allowed();
    }

    /**
     * @param \BetaKiller\Notification\NotificationMessageInterface $message
     * @param \BetaKiller\Notification\NotificationUserInterface    $user
     *
     * @return int Number of messages sent
     */
    public function send(NotificationMessageInterface $message, NotificationUserInterface $user)
    {
        $fromUser = $message->get_from();

        $from        = $fromUser ? $fromUser->get_email() : null;
        $to          = $user->get_email();
        $subj        = $message->get_subj($user);
        $attachments = $message->get_attachments();

        $body = $this->renderMessage($message, $user);

        // Email notification
        return \Email::send($from, $to, $subj, $body, true, $attachments);
    }
}
