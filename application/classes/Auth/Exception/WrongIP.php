<?php defined('SYSPATH') OR die('No direct script access.');

class Auth_Exception_WrongIP extends Auth_Exception
{
    protected function get_default_message()
    {
        return __('Your IP address is not valid');
    }
}
