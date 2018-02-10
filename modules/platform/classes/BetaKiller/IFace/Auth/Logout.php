<?php
namespace BetaKiller\IFace\Auth;

use BetaKiller\IFace\AbstractIFace;

class Logout extends AbstractIFace
{
    /**
     * @Inject
     * @var \Auth
     */
    private $auth;

    /**
     * @Inject
     * @var \BetaKiller\Helper\ResponseHelper
     */
    private $responseHelper;

    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     *
     * @throws \BetaKiller\Exception\FoundHttpException
     */
    public function before(): void
    {
        // Sign out the user
        $this->auth->logout(true);

        // Redirect to site index
        $this->responseHelper->redirect('/');
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        return [];
    }
}
