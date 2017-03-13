<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Notification\NotificationMessageInterface;
use BetaKiller\Notification\NotificationUserInterface;
use BetaKiller\Notification\TransportInterface;

class EmailTransport extends AbstractTransport implements TransportInterface
{
    const NAME = 'email';

    public function get_name()
    {
        return self::NAME;
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
        $subj = $message->get_subj();
        $attachments = $message->get_attachments();

        $body = $this->renderMessage($message);

        $fromUser = $message->get_from();

        // Email notification
        return \Email::send(
            $fromUser ? $fromUser->get_email() : NULL,
            $user->get_email(),
            $subj,
            $body,
            TRUE,
            $attachments
        );
    }
}
