<?php

class Auth_Exception_UserDoesNotExists extends Auth_Exception
{
    public function getDefaultMessageI18nKey(): string
    {
        return 'error.auth.user_not_exists';
    }

    public function isNotificationEnabled(): bool
    {
        // Notify admin when incorrect username used
        return true;
    }
}
