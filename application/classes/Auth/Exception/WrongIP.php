<?php defined('SYSPATH') OR die('No direct script access.');

class Auth_Exception_WrongIP extends Auth_Exception
{
    protected function getDefaultMessageI18nKey()
    {
        return 'error.auth.wrong_ip';
    }
}
