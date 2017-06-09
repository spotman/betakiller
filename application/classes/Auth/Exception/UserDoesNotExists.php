<?php defined('SYSPATH') OR die('No direct script access.');

class Auth_Exception_UserDoesNotExists extends Auth_Exception
{
    protected function getDefaultMessageI18nKey()
    {
        return 'error.auth.user_not_exists';
    }

    public function isNotificationEnabled()
    {
        // Notify admin when incorrect username used
        return true;
    }
}
