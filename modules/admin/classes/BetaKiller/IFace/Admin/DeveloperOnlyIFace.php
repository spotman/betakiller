<?php
namespace BetaKiller\IFace\Admin;

abstract class DeveloperOnlyIFace extends AdminBase
{
    protected function checkIfacePermissions()
    {
        return $this->user->isDeveloper();
    }
}
