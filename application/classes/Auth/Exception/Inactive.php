<?php

class Auth_Exception_Inactive extends Auth_Exception
{
    /**
     * @return string
     */
    public function getDefaultMessageI18nKey(): string
    {
        return 'error.auth.inactive';
    }
}
