<?php
namespace BetaKiller\Auth;

class AccessDeniedException extends \HTTP_Exception_403
{
    /**
     * Returns default message for current exception
     * Allows throwing concrete exception without message
     * Useful for custom exception types
     * Return null if no default message allowed
     *
     * @return string
     */
    public function getDefaultMessageI18nKey(): ?string
    {
        return 'error.auth.denied';
    }
}
