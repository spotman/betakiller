<?php defined('SYSPATH') OR die('No direct script access.');

class IFace_Auth_Logout extends IFace {

    public function render()
    {
        // Sign out the user
        Env::auth()->logout(TRUE);

        // Redirect to site index
        HTTP::redirect();
    }

}