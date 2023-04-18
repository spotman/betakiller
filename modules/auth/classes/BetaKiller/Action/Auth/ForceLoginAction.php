<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Auth\AuthorizationRequiredException;
use BetaKiller\Auth\IncorrectCredentialsException;
use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Event\WebLoginEvent;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Service\AuthService;
use BetaKiller\Url\Parameter\UserNameUrlParameter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spotman\Acl\AclInterface;

/**
 * Class ForceLoginAction
 * Action for force logging in via URL
 *
 * @package BetaKiller\Auth
 */
final class ForceLoginAction extends AbstractAction
{
    /**
     * @var \BetaKiller\Service\AuthService
     */
    private AuthService $auth;

    /**
     * @var \BetaKiller\MessageBus\EventBusInterface
     */
    private EventBusInterface $eventBus;

    /**
     * @var \BetaKiller\Auth\UserUrlDetectorInterface
     */
    private UserUrlDetectorInterface $urlDetector;

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private UserRepositoryInterface $userRepo;

    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * @var \Spotman\Acl\AclInterface
     */
    private AclInterface $acl;

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
        AppEnvInterface $appEnv,
        AuthService $auth,
        UserRepositoryInterface $userRepo,
        UserUrlDetectorInterface $urlDetector,
        AclInterface $acl,
        EventBusInterface $eventBus
    ) {
        $this->appEnv      = $appEnv;
        $this->auth        = $auth;
        $this->userRepo    = $userRepo;
        $this->eventBus    = $eventBus;
        $this->urlDetector = $urlDetector;
        $this->acl         = $acl;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\UserBlockedException
     * @throws \BetaKiller\Auth\IncorrectCredentialsException
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
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

        // Fetch User name from request URL (no direct User model binding via ID coz ID is obfuscated in stage)
        $userName = ServerRequestHelper::getParameter($request, UserNameUrlParameter::class);

        $user = $this->userRepo->searchBy($userName->getValue());

        if (!$user) {
            throw new IncorrectCredentialsException;
        }

        $this->auth->login($session, $user);

        // Notify other subsystems
        $this->eventBus->emit(new WebLoginEvent($user, ServerRequestHelper::getUrlContainer($request)));

        return ResponseHelper::redirect($this->urlDetector->detect($user));
    }
}
