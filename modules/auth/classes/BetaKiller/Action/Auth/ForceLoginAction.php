<?php
declare(strict_types=1);

namespace BetaKiller\Action\Auth;

use BetaKiller\Action\AbstractAction;
use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Auth\IncorrectCredentialsException;
use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Event\WebLoginEvent;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Service\AuthService;
use BetaKiller\Url\Parameter\UserNameUrlParameter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * ForceLoginAction constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface             $appEnv
     * @param \BetaKiller\Service\AuthService                $auth
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \BetaKiller\Auth\UserUrlDetectorInterface      $urlDetector
     * @param \BetaKiller\MessageBus\EventBusInterface       $eventBus
     */
    public function __construct(
        AppEnvInterface $appEnv,
        AuthService $auth,
        UserRepositoryInterface $userRepo,
        UserUrlDetectorInterface $urlDetector,
        EventBusInterface $eventBus
    ) {
        $this->appEnv      = $appEnv;
        $this->auth        = $auth;
        $this->userRepo    = $userRepo;
        $this->eventBus    = $eventBus;
        $this->urlDetector = $urlDetector;
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
        $session = ServerRequestHelper::getSession($request);

        $isRestrictedEnv     = $this->appEnv->inProductionMode() || $this->appEnv->inStagingMode();
        $isForceLoginAllowed = SessionHelper::hasRoleName($session, RoleInterface::FORCE_LOGIN);

        if ($isRestrictedEnv && !$isForceLoginAllowed) {
            throw new AccessDeniedException();
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
