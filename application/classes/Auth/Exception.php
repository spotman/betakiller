<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Auth_Exception extends HTTP_Exception_403 {

    /**
     * @return string
     */
    protected function getDefaultMessage()
    {
        return 'Authentication failed';
    }

}
