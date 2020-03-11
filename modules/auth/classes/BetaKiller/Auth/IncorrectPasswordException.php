<?php
namespace BetaKiller\Auth;

class IncorrectPasswordException extends AbstractAuthException
{
    public function getDefaultMessageI18nKey(): ?string
    {
        return 'error.auth.incorrect-password';
    }
}
