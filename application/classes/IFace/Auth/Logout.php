<?php defined('SYSPATH') OR die('No direct script access.');

use \BetaKiller\IFace\IFace;

class IFace_Auth_Logout extends IFace {

    public function render()
    {
        // Sign out the user
        Env::auth()->logout(TRUE);

        // Redirect to site index
        $this->redirect('/');
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function get_data()
    {
        return [];
    }

}
