<?php defined('SYSPATH') OR die('No direct script access.');

class Auth_Exception_UserDoesNotExists extends Auth_Exception
{
    protected function getDefaultMessage()
    {
        return 'User does not exists';
    }
}
