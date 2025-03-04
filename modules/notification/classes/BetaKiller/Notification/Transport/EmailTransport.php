<?php

namespace BetaKiller\Notification\Transport;

use BetaKiller\Config\EmailConfigInterface;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Notification\MessageInterface;
use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Notification\MessageTargetResolverInterface;
use BetaKiller\Notification\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final readonly class EmailTransport extends AbstractTransport
{
    /**
     * EmailTransport constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface                         $appEnv
     * @param \Symfony\Component\Mailer\MailerInterface               $mailer
     * @param \BetaKiller\Config\EmailConfigInterface                 $config
     * @param \BetaKiller\Notification\MessageTargetResolverInterface $resolver
     */
    public function __construct(
        private AppEnvInterface $appEnv,
        private MailerInterface $mailer,
        private EmailConfigInterface $config,
        private MessageTargetResolverInterface $resolver
    ) {
    }

    public static function getName(): string
    {
        return 'email';
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
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     * @throws \BetaKiller\Notification\TransportException
     */
    public function send(
        MessageInterface $message,
        MessageTargetInterface $target,
        string $body
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
            throw new TransportException('Missing email target');
        }

        if (!$subj) {
            throw new TransportException('Missing email subject');
        }

        // Redirect all emails if required
        if (!$this->resolver->isDirectSendingAllowed($target)) {
            $subj .= ' [DEBUG] '.$to;
            $to   = $this->appEnv->getDebugEmail();
        }

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

        // Fake delay to prevent blackout of SMTP relay
        usleep(500000);

        return true;
    }
}
