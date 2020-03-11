<?php
namespace BetaKiller\Auth;

class IncorrectCredentialsException extends AbstractAuthException
{
    public function getDefaultMessageI18nKey(): ?string
    {
        return 'error.auth.incorrect-credentials';
    }
}
