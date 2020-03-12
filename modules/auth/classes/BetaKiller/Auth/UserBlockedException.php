<?php
namespace BetaKiller\Auth;

class UserBlockedException extends AbstractAuthException
{
    /**
     * @return string
     */
    public function getDefaultMessageI18nKey(): ?string
    {
        return 'error.auth.blocked';
    }
}
