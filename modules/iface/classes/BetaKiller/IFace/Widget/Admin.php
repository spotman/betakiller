<?php
namespace BetaKiller\IFace\Widget;

use BetaKiller\IFace\Widget;

abstract class Admin extends Widget
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
}
