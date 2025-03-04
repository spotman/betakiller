<?php

namespace BetaKiller\Notification;

final readonly class PhoneMessageTarget implements PhoneMessageTargetInterface
{
    /**
     * PhoneMessageTarget constructor.
     *
     * @param string $phone
     * @param string $langIsoCode
     */
    public function __construct(private string $phone, private string $langIsoCode)
    {
    }

    public function getMessageIdentity(): string
    {
        return sprintf('+%s', trim($this->getMessagePhone(), '+'));
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return '';
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

    public function getMessagePhone(): string
    {
        return $this->phone;
    }

    public function isPhoneNotificationAllowed(): bool
    {
        return true;
    }
}
