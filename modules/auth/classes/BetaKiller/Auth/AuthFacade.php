<?php
declare(strict_types=1);

namespace BetaKiller\Auth;

use BetaKiller\Factory\GuestUserFactory;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\RoleRepository;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Session\SessionStorageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Session\SessionInterface;

class AuthFacade
{
    /**
     * @var \BetaKiller\Session\SessionStorageInterface
     */
    private $sessionStorage;

    /**
     * @var \BetaKiller\Auth\SessionConfig
     */
    private $config;

    /**
     * @var \BetaKiller\Factory\GuestUserFactory
     */
    private $guestUserFactory;

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepo;

    /**
     * @var \BetaKiller\Repository\RoleRepository
     */
    private $roleRepo;

    /**
     * Loads Session and configuration options.
     *
     * @param \BetaKiller\Auth\SessionConfig              $config Config Options
     * @param \BetaKiller\Session\SessionStorageInterface $sessionStorage
     * @param \BetaKiller\Factory\GuestUserFactory        $guestUserFactory
     * @param \BetaKiller\Repository\UserRepository       $userRepo
     * @param \BetaKiller\Repository\RoleRepository       $roleRepo
     */
    public function __construct(
        SessionConfig $config,
        SessionStorageInterface $sessionStorage,
        GuestUserFactory $guestUserFactory,
        UserRepository $userRepo,
        RoleRepository $roleRepo
    ) {
        $this->config           = $config;
        $this->sessionStorage   = $sessionStorage;
        $this->guestUserFactory = $guestUserFactory;
        $this->userRepo         = $userRepo;
        $this->roleRepo         = $roleRepo;
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

        return \count($sessions) > 0;
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

    public function getUserFromSessionID(string $sessionID): UserInterface
    {
        $session = $this->getSession($sessionID);

        return $this->getSessionUser($session);
    }

    public function getSessionFromRequest(ServerRequestInterface $request): SessionInterface
    {
        return ServerRequestHelper::getSession($request);
    }

    public function getUserFromRequest(ServerRequestInterface $request): UserInterface
    {
        $session = $this->getSessionFromRequest($request);

        return $this->getSessionUser($session);
    }

    /**
     * Attempt to log in a user by using an ORM object and plain-text password.
     *
     * @param \BetaKiller\Model\UserInterface          $user
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function login(
        UserInterface $user,
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
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

        // Get current session
        $session = $this->getSessionFromRequest($request);

//        $session->regenerate();

        // Store user in session
        SessionHelper::setUserID($session, $user);

        // Save session to place session ID to database
        return $this->sessionStorage->persistSession($session, $response);
    }

    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $session = $this->getSessionFromRequest($request);

        $user = $this->getSessionUser($session);

        if ($user->isGuest()) {
            // Nothing to do for guest user
            return $response;
        }

        // Regenerate session to delete session record and generate new token
        $session->regenerate();

        return $this->sessionStorage->persistSession($session, $response);
    }

    /**
     * Compare password with original (hashed).
     *
     * @param \BetaKiller\Model\UserInterface $user
     * @param   string                        $password
     *
     * @return  boolean
     */
    public function checkPassword(UserInterface $user, string $password): bool
    {
        if (empty($password)) {
            return false;
        }

        return $this->makePasswordHash($password) === $user->getPassword();
    }

    /**
     * Perform a hmac hash, using the configured method.
     *
     * @param   string $str string to hash
     *
     * @return  string
     */
    private function makePasswordHash($str): string
    {
        return hash_hmac($this->config->getHashMethod(), $str, $this->config->getHashKey());
    }
}
