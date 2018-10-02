<?php
namespace BetaKiller\IFace\Auth;

use BetaKiller\Auth\AuthFacade;
use BetaKiller\Helper\RequestHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\UrlContainerHelper;
use BetaKiller\Model\UserInterface;

class ReLogin extends Login
{
    /**
     * @var \BetaKiller\Auth\AuthFacade
     */
    private $auth;

    public function __construct(
        AuthFacade $auth,
        UserInterface $user,
        RequestHelper $reqHelper,
        ResponseHelper $respHelper,
        UrlContainerHelper $urlParamsHelper
    ) {
        $this->auth = $auth;

        parent::__construct($user, $reqHelper, $respHelper, $urlParamsHelper);

        $this->redirectUrl = '/';
    }

    public function before(): void
    {
        // If user is logged in
        if (!$this->user->isGuest()) {
            // Sign out the user
            $this->auth->logout($this->user, true);
        }
    }
}
