<?php
namespace BetaKiller\Helper;

trait CurrentUserTrait
{
    protected function current_user($allow_guest = FALSE)
    {
        return \Env::user($allow_guest);
    }
}
