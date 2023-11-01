<?php
namespace BetaKiller\Notification\Transport;

use BetaKiller\Config\EmailConfigInterface;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Notification\MessageInterface;
use BetaKiller\Notification\MessageTargetInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class EmailTransport extends AbstractTransport
{
    public const CODENAME = 'email';

    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \Symfony\Component\Mailer\MailerInterface
     */
    private MailerInterface $mailer;

    /**
     * @var \BetaKiller\Config\EmailConfigInterface
     */
    private EmailConfigInterface $config;

    /**
     * EmailTransport constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface           $appEnv
     * @param \Symfony\Component\Mailer\MailerInterface $mailer
     * @param \BetaKiller\Config\EmailConfigInterface   $config
     */
    public function __construct(AppEnvInterface $appEnv, MailerInterface $mailer, EmailConfigInterface $config)
    {
        $this->appEnv = $appEnv;
        $this->mailer = $mailer;
        $this->config = $config;
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
        MessageInterface       $message,
        MessageTargetInterface $target,
        string                 $body
    ): bool {
        $fromUser = $message->getFrom();

        $sender = new Address($this->config->getFromEmail(), $this->config->getFromName());

        $from = $fromUser
            ? new Address($fromUser->getEmail(), $fromUser->getFullName())
            : $sender;

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

        $email = (new Email())
            ->sender($sender)
            ->from($from)
            ->to($to)
            ->subject($subj)
            ->html($body)
            ->priority($message->isCritical() ? 1 : 5);

        foreach ($attachments as $attach) {
            $email->attachFromPath($attach, basename($attach));
        }

        $this->mailer->send($email);

        return true;
    }
}
