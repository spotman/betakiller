<?php
namespace BetaKiller\Notification;

class NotificationUserEmail implements NotificationUserInterface
{
    /**
     * @var string
     */
    private $email;

    /**
     * @var string|null
     */
    private $fullName;

    /**
     * @var bool
     */
    private $emailNotificationAllowed = true;

    /**
     * @var string|null
     */
    private $langName;

    public function __construct(string $email, string $fullName, string $langName = null)
    {
        $this->email    = $email;
        $this->fullName = $fullName;
        $this->langName = $langName;
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
     * @return null|string
     */
    public function getLanguageName(): ?string
    {
        return $this->langName;
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
