<?php defined('SYSPATH') OR die('No direct access allowed.');

class Auth_Exception_IncorrectPassword extends Auth_Exception
{
    protected function get_default_message()
    {
        return __('Your credentials are invalid');
    }
}
