<?php
namespace BetaKiller\Auth;

class InactiveException extends AbstractAuthException
{
    /**
     * @return string
     */
    public function getDefaultMessageI18nKey(): ?string
    {
        return 'error.auth.inactive';
    }
}
