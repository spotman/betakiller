<?php
namespace BetaKiller\IFace\Auth;

use Auth;
use BetaKiller\Helper\IFaceHelper;
use BetaKiller\Helper\RequestHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\UrlContainerHelper;
use BetaKiller\Model\UserInterface;

class ReLogin extends Login
{
    /**
     * @var \Auth
     */
    private $auth;

    public function __construct(
        Auth $auth,
        UserInterface $user,
        RequestHelper $reqHelper,
        ResponseHelper $respHelper,
        UrlContainerHelper $urlParamsHelper,
        IFaceHelper $ifaceHelper
    ) {
        $this->auth = $auth;

        parent::__construct($ifaceHelper, $user, $reqHelper, $respHelper, $urlParamsHelper);

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
