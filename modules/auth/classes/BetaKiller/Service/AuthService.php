<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Acl\Resource\UserResource;
use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Auth\UserBlockedException;
use BetaKiller\Config\SessionConfigInterface;
use BetaKiller\Event\UserPasswordChangedEvent;
use BetaKiller\Event\UserPasswordChangeRequestedEvent;
use BetaKiller\Factory\GuestUserFactory;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\Model\User;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Session\SessionStorageInterface;
use Spotman\Acl\AclInterface;
use Zend\Expressive\Session\SessionInterface;

class AuthService
{
    public const PASSWORD_MIN_LENGTH = 8;
    public const PASSWORD_MAX_LENGTH = 50;

    /**
     * @var \BetaKiller\Config\SessionConfigInterface
     */
    private SessionConfigInterface $config;

    /**
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private UserRepositoryInterface $userRepo;

    /**
     * @var \BetaKiller\Session\SessionStorageInterface
     */
    private SessionStorageInterface $sessionStorage;

    /**
     * @var \BetaKiller\Factory\GuestUserFactory
     */
    private $guestUserFactory;

    /**
     * @var \BetaKiller\MessageBus\EventBusInterface
     */
    private EventBusInterface $eventBus;

    /**
     * @var \Spotman\Acl\AclInterface
     */
    private AclInterface $acl;

    /**
     * AuthService constructor.
     *
     * @param \BetaKiller\Config\SessionConfigInterface      $config
     * @param \BetaKiller\Session\SessionStorageInterface    $sessionStorage
     * @param \BetaKiller\Factory\GuestUserFactory           $guestUserFactory
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \Spotman\Acl\AclInterface                      $acl
     * @param \BetaKiller\MessageBus\EventBusInterface       $eventBus
     */
    public function __construct(
        SessionConfigInterface $config,
        SessionStorageInterface $sessionStorage,
        GuestUserFactory $guestUserFactory,
        UserRepositoryInterface $userRepo,
        AclInterface $acl,
        EventBusInterface $eventBus
    ) {
        $this->config           = $config;
        $this->sessionStorage   = $sessionStorage;
        $this->guestUserFactory = $guestUserFactory;
        $this->userRepo         = $userRepo;
        $this->acl              = $acl;
        $this->eventBus         = $eventBus;
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
        if (!SessionHelper::hasUserID($session)) {
            return $this->guestUserFactory->create();
        }

        // Extract user from session
        $userID = SessionHelper::getUserID($session);

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
     * @throws \BetaKiller\Auth\UserBlockedException
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function login(
        SessionInterface $session,
        UserInterface $user
    ): void {
        // Check account is active
        if (!$user->isActive()) {
            throw new UserBlockedException();
        }

        $userResource = $this->acl->getResource(User::getModelName());

        // No login role => user is not allowed to login
        if (!$this->acl->isAllowedToUser($userResource, UserResource::ACTION_LOGIN, $user)) {
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
        $this->eventBus->emit(new UserPasswordChangeRequestedEvent($user));
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

        $this->eventBus->emit(new UserPasswordChangedEvent($user));
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
