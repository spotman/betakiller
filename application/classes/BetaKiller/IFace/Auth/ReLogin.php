<?php namespace BetaKiller\IFace\Auth;

defined('SYSPATH') OR die('No direct script access.');

use Auth;
use BetaKiller\Model\UserInterface;

class ReLogin extends Login
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

    public function before(): void
    {
        // If user is logged in
        if (!$this->user->isGuest()) {
            // Sign out the user
            $this->auth->logout(true);
        }
    }
}
