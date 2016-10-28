<?php defined('SYSPATH') OR die('No direct script access.');

class IFace_Auth_ReLogin extends IFace_Auth_Login {

    public function render()
    {
        // TODO DI

        // If user is logged in
        if ( Env::user(TRUE) )
        {
            // Sign out the user
            Env::auth()->logout(TRUE);

            // Redirect to current url
            HTTP::redirect($this->url());
        }

        return parent::render();
    }

}
