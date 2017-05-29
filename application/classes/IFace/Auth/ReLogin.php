<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\Model\UserInterface;

class IFace_Auth_ReLogin extends IFace_Auth_Login
{
    /**
     * @var \Auth
     */
    private $auth;

    public function __construct(Auth $auth, UserInterface $user)
    {
        $this->auth = $auth;

        parent::__construct($user);

        $this->setRedirectUrl('/');
    }

    public function before()
    {
        // If user is logged in
        if (!$this->user->isGuest()) {
            // Sign out the user
            $this->auth->logout(TRUE);
        }
    }
}
