<?php
namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Service\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogoutAction extends AbstractAction
{
    /**
     * @var \BetaKiller\Service\AuthService
     */
    private $auth;

    /**
     * LogoutAction constructor.
     *
     * @param \BetaKiller\Service\AuthService $auth
     */
    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $session = ServerRequestHelper::getSession($request);

        // Sign out the user and get fresh session
        $this->auth->logout($session);

        // Redirect to site index
        return ResponseHelper::redirect('/');
    }
}
