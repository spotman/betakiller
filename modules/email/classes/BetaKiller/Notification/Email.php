<?php
namespace BetaKiller\Notification;

class NotificationUserEmail implements NotificationUserInterface
{
    protected $_email;

    public function get_id()
    {
        // No ID for direct email sending
        return NULL;
    }

    public static function factory($email)
    {
        return new static($email);
    }

    public function __construct($email)
    {
        $this->_email = $email;
    }

    public function is_online()
    {
        return FALSE;
    }

    public function get_email()
    {
        return $this->_email;
    }

    public function is_email_notification_allowed()
    {
        return TRUE;
    }

    public function is_online_notification_allowed()
    {
        return FALSE;
    }
};
