<?php defined('SYSPATH') OR die('No direct access allowed.');

class Auth_Exception_IncorrectPassword extends Auth_Exception
{
    public function getDefaultMessageI18nKey(): string
    {
        return 'error.auth.incorrect_password';
    }
}
