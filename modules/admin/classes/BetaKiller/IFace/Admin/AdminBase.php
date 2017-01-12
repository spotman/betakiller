<?php
namespace BetaKiller\IFace\Admin;

use BetaKiller\IFace\IFace;

abstract class AdminBase extends IFace
{
    public function before()
    {
        if (!$this->check_iface_permissions())
            throw new \HTTP_Exception_403('Permission denied');
    }

    protected function check_iface_permissions()
    {
        // Force authorization
        return $this->current_user()->is_admin_allowed();
    }

    public function getDefaultExpiresInterval()
    {
        $interval = new \DateInterval('PT1H');
        $interval->invert = 1;
        return $interval;
    }
}
