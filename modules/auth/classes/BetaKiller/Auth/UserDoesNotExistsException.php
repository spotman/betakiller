<?php
namespace BetaKiller\Auth;

class UserDoesNotExistsException extends AbstractAuthException
{
    public function getDefaultMessageI18nKey(): ?string
    {
        return 'error.auth.user-not-exists';
    }

    public function isNotificationEnabled(): bool
    {
        // Notify admin when incorrect username used
        return true;
    }
}
