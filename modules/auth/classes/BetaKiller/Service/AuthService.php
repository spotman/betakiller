<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Action\Auth\ClaimRegistrationAction;
use BetaKiller\Action\Auth\VerifyPasswordChangeTokenAction;
use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Auth\InactiveException;
use BetaKiller\Auth\SessionConfig;
use BetaKiller\Factory\GuestUserFactory;
use BetaKiller\Factory\UrlHelperFactory;
use BetaKiller\Helper\NotificationHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\RoleRepository;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Session\SessionStorageInterface;
use DateInterval;
use Zend\Expressive\Session\SessionInterface;

class AuthService
{
    public const REQUEST_PASSWORD_CHANGE = 'auth/password-change-request';

    public const PASSWORD_MIN_LENGTH = 8;
    public const PASSWORD_MAX_LENGTH = 50;

    /**
     * @var \BetaKiller\Auth\SessionConfig
     */
    private $config;

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * @var \BetaKiller\Repository\RoleRepository
     */
    private $roleRepo;

    /**
     * @var \BetaKiller\Session\SessionStorageInterface
     */
    private $sessionStorage;

    /**
     * @var \BetaKiller\Factory\GuestUserFactory
     */
    private $guestUserFactory;

    /**
     * @var \BetaKiller\Helper\NotificationHelper
     */
    private $notification;

    /**
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * @var \BetaKiller\Service\TokenService
     */
    private $tokenService;

    /**
     * AuthService constructor.
     *
     * @param \BetaKiller\Auth\SessionConfig              $config
     * @param \BetaKiller\Session\SessionStorageInterface $sessionStorage
     * @param \BetaKiller\Factory\GuestUserFactory        $guestUserFactory
     * @param \BetaKiller\Repository\UserRepository       $userRepo
     * @param \BetaKiller\Repository\RoleRepository       $roleRepo
     * @param \BetaKiller\Helper\NotificationHelper       $notification
     * @param \BetaKiller\Service\TokenService            $tokenService
     * @param \BetaKiller\Factory\UrlHelperFactory        $urlHelperFactory
     */
    public function __construct(
        SessionConfig $config,
        SessionStorageInterface $sessionStorage,
        GuestUserFactory $guestUserFactory,
        UserRepository $userRepo,
        RoleRepository $roleRepo,
        NotificationHelper $notification,
        TokenService $tokenService,
        UrlHelperFactory $urlHelperFactory
    ) {
        $this->config           = $config;
        $this->sessionStorage   = $sessionStorage;
        $this->guestUserFactory = $guestUserFactory;
        $this->userRepo         = $userRepo;
        $this->roleRepo         = $roleRepo;
        $this->notification     = $notification;
        $this->tokenService     = $tokenService;
        $this->urlHelper        = $urlHelperFactory->create();
    }

    public function searchBy(string $loginOrEmail): ?UserInterface
    {
        return $this->userRepo->searchBy($loginOrEmail);
    }

    /**
     * Checks if a user session is active.
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return  boolean
     */
    public function isUserLoggedIn(UserInterface $user): bool
    {
        $sessions = $this->sessionStorage->getUserSessions($user);

        return count($sessions) > 0;
    }

    public function getSessionUser(SessionInterface $session): UserInterface
    {
        // Extract user from session
        $userID = SessionHelper::getUserID($session);

        if (!$userID) {
            return $this->guestUserFactory->create();
        }

        return $this->userRepo->getById($userID);
    }

    public function getSession(string $sessionID): SessionInterface
    {
        return $this->sessionStorage->getByToken($sessionID);
    }

    /**
     * Attempt to log in a user by using an ORM object and plain-text password.
     *
     * @param \Zend\Expressive\Session\SessionInterface $session
     * @param \BetaKiller\Model\UserInterface           $user
     *
     * @return void
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\InactiveException
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function login(
        SessionInterface $session,
        UserInterface $user
    ): void {
        // Check account is active
        if (!$user->isActive()) {
            throw new InactiveException();
        }

        $loginRole = $this->roleRepo->getLoginRole();

        // No login role => user is not allowed to login
        if (!$user->hasRole($loginRole)) {
            throw new AccessDeniedException();
        }

        // Run custom user update
        $user->completeLogin();
        $this->userRepo->save($user);

        // Store user in session
        SessionHelper::setUserID($session, $user);

        // Always create new session on successful login to prevent stale sessions
        $session->regenerate();
        // Session will be saved in SessionMiddleware
    }

    public function logout(SessionInterface $session): void
    {
        $user = $this->getSessionUser($session);

        if (!$user->isGuest()) {
            // Detach session from user
            SessionHelper::removeUserID($session);

            // Regenerate session to delete session record and generate new token
            $session->regenerate();
        }
        // Session will be saved in SessionMiddleware
    }

    public function requestPasswordChange(UserInterface $user): void
    {
        $token = $this->tokenService->create($user, new DateInterval('PT8H'));

        $params      = $this->urlHelper->createUrlContainer()->setEntity($token);
        $action      = $this->urlHelper->getUrlElementByCodename(VerifyPasswordChangeTokenAction::codename());
        $claimAction = $this->urlHelper->getUrlElementByCodename(ClaimRegistrationAction::codename());

        $this->notification->directMessage(self::REQUEST_PASSWORD_CHANGE, $user, [
            'action_url' => $this->urlHelper->makeUrl($action, $params, false),
            'claim_url'  => $this->urlHelper->makeUrl($claimAction),
        ]);
    }

    /**
     * Compare password with original (hashed).
     *
     * @param \BetaKiller\Model\UserInterface $user
     * @param string                          $password
     *
     * @return  boolean
     */
    public function checkPassword(UserInterface $user, string $password): bool
    {
        if (empty($password)) {
            return false;
        }

        // No password defined => no login allowed
        if (!$user->hasPassword()) {
            return false;
        }

        return $this->makePasswordHash($password) === $user->getPassword();
    }

    public function updateUserPassword(UserInterface $user, string $password): void
    {
        $hash = $this->makePasswordHash($password);
        $user->setPassword($hash);

        $this->userRepo->save($user);
    }

    /**
     * Perform a hmac hash, using the configured method.
     *
     * @param string $str string to hash
     *
     * @return  string
     */
    private function makePasswordHash(string $str): string
    {
        return hash_hmac($this->config->getHashMethod(), $str, $this->config->getHashKey());
    }
}
