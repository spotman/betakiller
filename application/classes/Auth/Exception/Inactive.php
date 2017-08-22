<?php

class Auth_Exception_Inactive extends Auth_Exception
{
    /**
     * @return string
     */
    protected function getDefaultMessageI18nKey(): string
    {
        return 'error.auth.inactive';
    }
}
