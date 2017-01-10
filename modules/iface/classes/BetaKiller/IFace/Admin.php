<?php
namespace BetaKiller\IFace;

abstract class Admin extends IFace
{
    public function render()
    {
        if (!$this->check_iface_permissions())
            throw new \HTTP_Exception_403('Permission denied');

        return parent::render();
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
