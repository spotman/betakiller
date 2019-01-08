<?php
namespace BetaKiller\Notification;

class NotificationTargetEmail implements NotificationTargetInterface
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $fullName;

    /**
     * @var bool
     */
    private $emailNotificationAllowed = true;

    /**
     * @var string
     */
    private $langIsoCode;

    /**
     * NotificationTargetEmail constructor.
     *
     * @param string $email
     * @param string $fullName
     * @param string $langIsoCode
     */
    public function __construct(string $email, string $fullName, string $langIsoCode)
    {
        $this->email       = $email;
        $this->fullName    = $fullName;
        $this->langIsoCode = $langIsoCode;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getEmail(): string
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
        return $this->emailNotificationAllowed;
    }

    public function isOnlineNotificationAllowed(): bool
    {
        return false;
    }

    public function enableEmailNotification(): void
    {
        $this->emailNotificationAllowed = true;
    }

    public function disableEmailNotification(): void
    {
        $this->emailNotificationAllowed = false;
    }
}
