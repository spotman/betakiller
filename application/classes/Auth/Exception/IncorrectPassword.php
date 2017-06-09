<?php defined('SYSPATH') OR die('No direct access allowed.');

class Auth_Exception_IncorrectPassword extends Auth_Exception
{
    protected function getDefaultMessageI18nKey()
    {
        return 'error.auth.incorrect_password';
    }
}
