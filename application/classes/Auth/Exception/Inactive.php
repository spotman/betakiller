<?php defined('SYSPATH') OR die('No direct script access.');

class Auth_Exception_Inactive extends Auth_Exception
{
    /**
     * @return string
     */
    protected function getDefaultMessage()
    {
        return 'Your account was switched off';
    }
}
