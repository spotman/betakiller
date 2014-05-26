<?php defined('SYSPATH') OR die('No direct script access.');

interface Notification_User_Interface {

    /**
     * Returns TRUE if user is using the site now (so online notifications may be provided)
     * @return bool
     */
    public function is_online();

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
     * Returns TRUE if user allowed online notifications through WebSockets/AJAX/etc
     * @return bool
     */
    public function is_online_notification_allowed();

};