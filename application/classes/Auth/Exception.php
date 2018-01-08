<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Auth_Exception extends HTTP_Exception_403
{
    /**
     * @return string
     */
    public function getDefaultMessageI18nKey(): string
    {
        return 'error.auth.common';
    }

}
