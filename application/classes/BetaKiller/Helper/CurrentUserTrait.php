<?php
namespace BetaKiller\Helper;

/**
 * Trait CurrentUserTrait
 *
 * @package BetaKiller\Helper
 * @deprecated Use DI with \BetaKiller\Model\UserInterface
 */
trait CurrentUserTrait
{
    /**
     * Use DI with \BetaKiller\Model\UserInterface
     *
     * @param bool $allow_guest
     *
     * @return \BetaKiller\Model\UserInterface|NULL
     * @deprecated Use DI with \BetaKiller\Model\UserInterface
     */
    protected function current_user($allow_guest = FALSE)
    {
        return \Env::user($allow_guest);
    }
}
