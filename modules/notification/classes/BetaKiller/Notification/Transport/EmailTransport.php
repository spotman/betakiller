<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Notification\MessageInterface;
use BetaKiller\Notification\MessageTargetInterface;

final class EmailTransport extends AbstractTransport
{
    public const CODENAME = 'email';

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * EmailTransport constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv)
    {
        $this->appEnv = $appEnv;
    }

    public function getName(): string
    {
        return self::CODENAME;
    }

    public function isEnabledFor(MessageTargetInterface $user): bool
    {
        return $user->isEmailNotificationAllowed();
    }

    /**
     * Returns true if current transport can handle provided message
     *
     * @param \BetaKiller\Notification\MessageInterface $message
     *
     * @return bool
     */
    public function canHandle(MessageInterface $message): bool
    {
        // Any message can be handled if there is a template
        return true;
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
     * @param \BetaKiller\Notification\MessageInterface       $message
     * @param \BetaKiller\Notification\MessageTargetInterface $target
     * @param string                                          $body
     *
     * @return bool Number of messages sent
     * @throws \BetaKiller\Exception
     */
    public function send(
        MessageInterface $message,
        MessageTargetInterface $target,
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

        // Redirect all emails while in debug mode
        if ($this->appEnv->isDebugEnabled()) {
            $subj .= ' [DEBUG] '.$to;
            $to   = $this->appEnv->getDebugEmail();
        }

        // Fake delay to prevent blackout of SMTP relay
        sleep(2);

        // Email notification
        return (bool)\Email::send($from, $to, $subj, $body, true, $attachments);
    }
}
