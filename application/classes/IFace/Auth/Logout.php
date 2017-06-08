<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\IFace\AbstractIFace;

class IFace_Auth_Logout extends AbstractIFace
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

    public function render(): string
    {
        // Sign out the user
        $this->auth->logout(true);

        // Redirect to site index
        $this->responseHelper->redirect('/');

        return '';
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
