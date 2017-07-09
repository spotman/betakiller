<?php
namespace BetaKiller\Notification;

class NotificationUserEmail implements NotificationUserInterface
{
    /**
     * @var string
     */
    protected $_email;

    /**
     * @var string
     */
    protected $_fullName;

    /**
     * @var bool
     */
    protected $_emailNotificationAllowed = true;

    public static function factory(string $email, string $fullName): NotificationUserEmail
    {
        return new static($email, $fullName);
    }

    public function __construct(string $email, string $fullName)
    {
        $this->_email = $email;
        $this->_fullName = $fullName;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->_fullName;
    }

    public function is_online()
    {
        return FALSE;
    }

    public function getEmail(): string
    {
        return $this->_email;
    }

    public function isEmailNotificationAllowed(): bool
    {
        return TRUE;
    }

    public function isOnlineNotificationAllowed(): bool
    {
        return FALSE;
    }

    public function enableEmailNotification(): void
    {
        $this->_emailNotificationAllowed = true;
    }

    public function disableEmailNotification(): void
    {
        $this->_emailNotificationAllowed = false;
    }
}
