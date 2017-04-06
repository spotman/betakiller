<?php
namespace BetaKiller\IFace\Admin;

use BetaKiller\Helper\CurrentUserTrait;

abstract class DeveloperOnly extends AdminBase
{
    use CurrentUserTrait;

    protected function checkIfacePermissions()
    {
        return $this->current_user()->is_developer();
    }
}
