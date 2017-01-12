<?php defined('SYSPATH') OR die('No direct script access.');

class Auth_Exception_UserDoesNotExists extends Auth_Exception
{
    protected function get_default_message()
    {
        return 'User does not exists';
    }
}
