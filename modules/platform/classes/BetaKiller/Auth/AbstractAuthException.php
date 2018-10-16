<?php
namespace BetaKiller\Auth;

abstract class AbstractAuthException extends AccessDeniedException
{
    /**
     * @return string
     */
    public function getDefaultMessageI18nKey(): ?string
    {
        return 'error.auth.common';
    }
}
