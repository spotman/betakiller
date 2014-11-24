<?php defined('SYSPATH') OR die('No direct script access.');

abstract class BetaKiller_IFace_Admin extends IFace {

    public function render()
    {
        if ( ! $this->check_iface_permissions() )
            throw new HTTP_Exception_403('Permission denied');

        return parent::render();
    }

    protected function check_iface_permissions()
    {
        // Force authorization
        $user = Env::user();

        return $user->is_moderator() OR $user->is_developer();
    }

}