<?php

declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Acl\Resource\UserResource;
use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Auth\PasswordHasherInterface;
use BetaKiller\Auth\UserBannedException;
use BetaKiller\Event\UserPasswordChangedEvent;
use BetaKiller\Event\UserPasswordChangeRequestedEvent;
use BetaKiller\Factory\GuestUserFactory;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\Model\User;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\UserRepositoryInterface;
use BetaKiller\Session\SessionCause;
use BetaKiller\Session\SessionStorageInterface;
use Laminas\Diactoros\Response\EmptyResponse;
use Mezzio\Session\SessionInterface;
use Spotman\Acl\AclInterface;

class AuthService
{
    public const PASSWORD_MIN_LENGTH = 8;
    public const PASSWORD_MAX_LENGTH = 50;

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
    private GuestUserFactory $guestUserFactory;

    /**
     * @var \BetaKiller\MessageBus\EventBusInterface
     */
    private EventBusInterface $eventBus;

    /**
     * @var \Spotman\Acl\AclInterface
     */
    private AclInterface $acl;

    /**
     * @var \BetaKiller\Auth\PasswordHasherInterface
     */
    private PasswordHasherInterface $hasher;

    /**
     * AuthService constructor.
     *
     * @param \BetaKiller\Auth\PasswordHasherInterface       $hasher
     * @param \BetaKiller\Session\SessionStorageInterface    $sessionStorage
     * @param \BetaKiller\Factory\GuestUserFactory           $guestUserFactory
     * @param \BetaKiller\Repository\UserRepositoryInterface $userRepo
     * @param \Spotman\Acl\AclInterface                      $acl
     * @param \BetaKiller\MessageBus\EventBusInterface       $eventBus
     */
    public function __construct(
        PasswordHasherInterface $hasher,
        SessionStorageInterface $sessionStorage,
        GuestUserFactory $guestUserFactory,
        UserRepositoryInterface $userRepo,
        AclInterface $acl,
        EventBusInterface $eventBus
    ) {
        $this->hasher           = $hasher;
        $this->sessionStorage   = $sessionStorage;
        $this->guestUserFactory = $guestUserFactory;
        $this->userRepo         = $userRepo;
        $this->acl              = $acl;
        $this->eventBus         = $eventBus;
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
        return (bool)$this->getUserSession($user);
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

    public function getUserSession(UserInterface $user): ?SessionInterface
    {
        $sessions = $this->sessionStorage->getUserSessions($user);

        return array_pop($sessions);
    }

    public function getSession(string $sessionID): SessionInterface
    {
        return $this->sessionStorage->getByToken($sessionID);
    }

    public function persistSession(SessionInterface $session): void
    {
        // Yep, it`s ugly, thanks to vendor lib
        $this->sessionStorage->persistSession($session, new EmptyResponse());
    }

    /**
     * Attempt to log in a user by using an ORM object and plain-text password.
     *
     * @param \Mezzio\Session\SessionInterface $session
     * @param \BetaKiller\Model\UserInterface  $user
     *
     * @return void
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\UserBannedException
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function login(
        SessionInterface $session,
        UserInterface $user
    ): void {
        // Check account is active
        if ($user->inStateBanned()) {
            throw new UserBannedException();
        }

        $userResource = $this->acl->getResource(User::getModelName());

        // No login role => user is not allowed to log in
        $loginAction = UserResource::ACTION_LOGIN;

        if (!$this->acl->isAllowedToUser($userResource, $loginAction, $user)) {
            throw new AccessDeniedException('Action ":name" is not allowed to User ":id"', [
                ':name' => UserResource::ACTION_LOGIN,
                ':id'   => $user->getID(),
            ]);
        }

        // Run custom user update
        $user->completeLogin();
        $this->userRepo->save($user);

        // Store user in session
        SessionHelper::setUser($session, $user);
        SessionHelper::setCause($session, SessionCause::Auth);

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
     * @param string $str string to hash
     *
     * @return  string
     */
    private function makePasswordHash(string $str): string
    {
        return $this->hasher->proceed($str);
    }
}
