<?php

declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Auth\AuthorizationRequiredException;
use BetaKiller\Auth\IncorrectCredentialsException;
use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Event\WebLoginEvent;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Service\AuthService;
use BetaKiller\Url\Parameter\UserName;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Acl\AclInterface;

/**
 * Class ForceLoginAction
 * Action for force logging in via URL
 *
 * @package BetaKiller\Auth
 */
final readonly class ForceLoginAction extends AbstractAction
{
    /**
     * ForceLoginAction constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface                $appEnv
     * @param \BetaKiller\Service\AuthService                $auth
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \BetaKiller\Auth\UserUrlDetectorInterface      $urlDetector
     * @param \Spotman\Acl\AclInterface                      $acl
     * @param \BetaKiller\MessageBus\EventBusInterface       $eventBus
     */
    public function __construct(
        private AppEnvInterface $appEnv,
        private AuthService $auth,
        private UserRepositoryInterface $userRepo,
        private UserUrlDetectorInterface $urlDetector,
        private AclInterface $acl,
        private EventBusInterface $eventBus
    ) {
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Auth\IncorrectCredentialsException
     * @throws \BetaKiller\Auth\UserBannedException
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\MessageBus\MessageBusException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Url\Container\UrlContainerException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user    = ServerRequestHelper::getUser($request);
        $session = ServerRequestHelper::getSession($request);

        $isRestrictedEnv     = $this->appEnv->inProductionMode() || $this->appEnv->inStagingMode();
        $isForceLoginAllowed = $this->acl->hasAssignedRoleName($user, RoleInterface::FORCE_LOGIN);

        if ($isRestrictedEnv && !$isForceLoginAllowed) {
            throw new AuthorizationRequiredException();
        }

        if (!ServerRequestHelper::isGuest($request)) {
            // Force logout before login
            $this->auth->logout($session);
        }

        // Fetch Username from request URL (no direct User model binding via ID coz ID is obfuscated in stage)
        $userName = ServerRequestHelper::getParameter($request, UserName::class);

        $user = $this->userRepo->findByUsername($userName->getValue());

        if (!$user) {
            throw new IncorrectCredentialsException();
        }

        $this->auth->login($session, $user);

        // Notify other subsystems
        $this->eventBus->emit(new WebLoginEvent($user, ServerRequestHelper::getUrlContainer($request)));

        return ServerRequestHelper::isJsonPreferred($request)
            ? ResponseHelper::successJson()
            : ResponseHelper::redirect($this->urlDetector->detect($user));
    }
}
