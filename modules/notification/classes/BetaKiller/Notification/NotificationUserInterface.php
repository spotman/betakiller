<?php
namespace BetaKiller\Notification;

interface NotificationUserInterface
{
    /**
     * Returns user ID
     * @return int
     */
    public function get_id();

    /**
     * Returns user email
     * @return string
     */
    public function get_email();

    /**
     * Returns TRUE if user allowed notifications through email
     * @return bool
     */
    public function is_email_notification_allowed();

    /**
     * @return $this
     */
    public function enable_email_notification();

    /**
     * @return $this
     */
    public function disable_email_notification();

    /**
     * Returns TRUE if user allowed online notifications through WebSockets/AJAX/etc
     * @return bool
     */
    public function is_online_notification_allowed();
}
