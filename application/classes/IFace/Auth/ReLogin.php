<?php defined('SYSPATH') OR die('No direct script access.');

class IFace_Auth_ReLogin extends IFace_Auth_Login
{
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;

        parent::__construct();

        $this->redirect_to('/');
    }

    public function before()
    {
        // If user is logged in
        if ($this->current_user(TRUE)) {
            // Sign out the user
            $this->auth->logout(TRUE);
        }
    }
}
