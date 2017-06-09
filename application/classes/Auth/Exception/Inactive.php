<?php defined('SYSPATH') OR die('No direct script access.');

class Auth_Exception_Inactive extends Auth_Exception
{
    /**
     * @return string
     */
    protected function getDefaultMessageI18nKey()
    {
        return 'error.auth.inactive';
    }
}
