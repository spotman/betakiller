<?php

namespace BetaKiller\Notification;

use BetaKiller\Exception\LogicException;

final readonly class EmailMessageTarget implements EmailMessageTargetInterface
{
    /**
     * EmailMessageTarget constructor.
     *
     * @param string $email
     * @param string $fullName
     * @param string $langIsoCode
     */
    public function __construct(private string $email, private string $fullName, private string $langIsoCode)
    {
    }

    public function getMessageIdentity(): string
    {
        return sprintf('%s <%s>', $this->getFullName(), $this->getMessageEmail());
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getMessageEmail(): string
    {
        return $this->email;
    }

    /**
     * Return preferred language (used in templates)
     *
     * @return string
     */
    public function getLanguageIsoCode(): string
    {
        return $this->langIsoCode;
    }

    public function isEmailNotificationAllowed(): bool
    {
        return true;
    }

    public function enableEmailNotification(): void
    {
        throw new LogicException('Can not configure email delivery for static DTO');
    }

    public function disableEmailNotification(): void
    {
        throw new LogicException('Can not configure email delivery for static DTO');
    }
}
