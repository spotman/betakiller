<?php

/**
 * Class Auth_Exception_WrongIP
 * @deprecated
 */
class Auth_Exception_WrongIP extends Auth_Exception
{
    public function getDefaultMessageI18nKey(): string
    {
        return 'error.auth.wrong_ip';
    }
}
