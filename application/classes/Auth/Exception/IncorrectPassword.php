<?php defined('SYSPATH') OR die('No direct access allowed.');

class Auth_Exception_IncorrectPassword extends Auth_Exception
{
    protected function getDefaultMessage()
    {
        return 'Your credentials are invalid';
    }
}
