<?php
namespace BetaKiller\Auth;

use HTTP_Exception_403;

abstract class AbstractAuthException extends HTTP_Exception_403
{
    /**
     * @return string
     */
    public function getDefaultMessageI18nKey(): ?string
    {
        return 'error.auth.common';
    }
}
