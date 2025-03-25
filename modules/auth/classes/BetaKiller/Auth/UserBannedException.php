<?php
namespace BetaKiller\Auth;

class UserBannedException extends AbstractAuthException
{
    /**
     * @return string
     */
    public function getDefaultMessageI18nKey(): ?string
    {
        return 'error.auth.banned';
    }
}
