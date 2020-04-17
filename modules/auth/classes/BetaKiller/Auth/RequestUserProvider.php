<?php
declare(strict_types=1);

namespace BetaKiller\Auth;

use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\AuthService;
use Psr\Http\Message\ServerRequestInterface;

final class RequestUserProvider
{
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private ServerRequestInterface $request;

    /**
     * @var \BetaKiller\Service\AuthService
     */
    private AuthService $auth;

    /**
     * Cached User instance
     *
     * @var \BetaKiller\Model\UserInterface
     */
    private ?UserInterface $user = null;

    /**
     * RequestUserProvider constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \BetaKiller\Service\AuthService          $auth
     */
    public function __construct(ServerRequestInterface $request, AuthService $auth)
    {
        $this->request = $request;
        $this->auth    = $auth;
    }

    public function fetch(): UserInterface
    {
        if (!$this->user) {
            $this->user = $this->detect($this->request);
        }

        return $this->user;
    }

    private function detect(ServerRequestInterface $request): UserInterface
    {
        $session = ServerRequestHelper::getSession($request);

        $u    = RequestProfiler::begin($request, 'Fetch User from Session');
        $user = $this->auth->getSessionUser($session);
        RequestProfiler::end($u);

        // Prefetch all roles
        $r     = RequestProfiler::begin($request, 'Fetch User roles');
        $roles = $user->getAllUserRolesNames();
        RequestProfiler::end($r);

        // Store roles names in session for further usage
        SessionHelper::setRolesNames($session, $roles);

        return $user;
    }
}
