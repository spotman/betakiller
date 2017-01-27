<?php
namespace BetaKiller\Helper;

trait CurrentUser
{
    protected function current_user($allow_guest = FALSE)
    {
        return \Env::user($allow_guest);
    }
}
