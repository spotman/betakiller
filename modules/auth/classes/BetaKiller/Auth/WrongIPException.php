<?php
namespace BetaKiller\Auth;

/**
 * Class WrongIPException
 *
 * @deprecated
 */
class WrongIPException extends AbstractAuthException
{
    public function getDefaultMessageI18nKey(): ?string
    {
        return 'error.auth.wrong-ip';
    }
}
