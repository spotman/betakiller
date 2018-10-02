<?php
declare(strict_types=1);

namespace BetaKiller\Auth;

use BetaKiller\Exception;
use BetaKiller\Factory\GuestUserFactory;
use BetaKiller\Model\UserInterface;
use BetaKiller\Model\UserToken;
use BetaKiller\Repository\RoleRepository;
use BetaKiller\Repository\UserRepository;
use BetaKiller\Repository\UserTokenRepository;
use BetaKiller\Session\SessionInterface;
use BetaKiller\Session\SessionStorageInterface;
use Cookie;
use Psr\Http\Message\ServerRequestInterface;

class Auth
{
    public const SESSION_COOKIE           = 'sid';
    public const SESSION_COOKIE_DELIMITER = '~';
    public const SESSION_USER_AGENT       = 'user_agent';
    public const AUTO_LOGIN_COOKIE        = 'alt';

    /**
     * @var \BetaKiller\Session\SessionStorageInterface
     */
    private $sessionStorage;

    /**
     * @var array|\BetaKiller\Auth\AuthConfig
     */
    private $config;

    /**
     * @var \BetaKiller\Factory\GuestUserFactory
     */
    private $guestUserFactory;

    /**
     * @var \BetaKiller\Repository\UserTokenRepository
     */
    private $userTokenRepo;

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
     * @param \BetaKiller\Auth\AuthConfig                 $config Config Options
     *
     * @param \BetaKiller\Session\SessionStorageInterface $sessionStorage
     * @param \BetaKiller\Factory\GuestUserFactory        $guestUserFactory
     * @param \BetaKiller\Repository\UserRepository       $userRepo
     * @param \BetaKiller\Repository\UserTokenRepository  $userTokenRepo
     * @param \BetaKiller\Repository\RoleRepository       $roleRepo
     */
    public function __construct(
        AuthConfig $config,
        SessionStorageInterface $sessionStorage,
        GuestUserFactory $guestUserFactory,
        UserRepository $userRepo,
        UserTokenRepository $userTokenRepo,
        RoleRepository $roleRepo
    ) {
        $this->config           = $config;
        $this->sessionStorage   = $sessionStorage;
        $this->guestUserFactory = $guestUserFactory;
        $this->userTokenRepo    = $userTokenRepo;
        $this->userRepo         = $userRepo;
        $this->roleRepo         = $roleRepo;
    }

    /**
     * Checks if a user session is active.
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return  boolean
     * @throws \BetaKiller\Exception
     */
    public function isUserLoggedIn(UserInterface $user): bool
    {
        if (!$user->getSessionID()) {
            return false;
        }

        $session = $this->getUserSession($user);

        return (bool)$this->getSessionUser($session);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Session\SessionInterface
     */
    public function getUserSession(UserInterface $user): SessionInterface
    {
        $sessionID = $user->getSessionID();

        return $this->sessionStorage->getByID($sessionID);
    }

    public function getSessionUser(SessionInterface $session): ?UserInterface
    {
        $user = $session->get($this->config->getSessionKey());

        if ($user && !$user instanceof UserInterface) {
            throw new Exception('Session User must implement :interface', [
                ':interface' => UserInterface::class,
            ]);
        }

        return $user;
    }

    public function getUserFromSessionID(string $sessionID): UserInterface
    {
        $session = $this->sessionStorage->getByID($sessionID);

        return $this->getSessionUser($session) ?: $this->guestUserFactory->create();
    }

    public function getSessionFromRequest(ServerRequestInterface $request): SessionInterface
    {
        // Check "sid" cookie and search for session
        $session = $this->sessionStorage->initializeSessionFromRequest($request);

        if (!$session instanceof SessionInterface) {
            throw new Exception('Session must implement :interface', [
                ':interface' => SessionInterface::class,
            ]);
        }

        return $session;
    }

    public function getUserFromRequest(ServerRequestInterface $request): UserInterface
    {
        $session = $this->getSessionFromRequest($request);

        $user = $this->getSessionUser($session);

        // If user detected => exiting
        if ($user) {
            return $user;
        }

        // Trying to autodetect user by auto-login cookie ("remember me" checkbox)
        $user = $this->checkAutoLoginToken($request, $session);

        if ($user) {


            return $user;
        }

        // No user detected => use GuestUser
        $user = $this->guestUserFactory->create();

        $user->setSessionID($session->getId());
        $this->setSessionUser($session, $user);
        $this->setSessionCookie($session);

        // Store user-agent
        $userAgent = $this->getUserAgent($request);
        $this->setSessionUserAgent($session, $userAgent);

        return $user;
    }

    /**
     * Logs a user in, based on the authautologin cookie.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \BetaKiller\Session\SessionInterface     $session
     *
     * @return  UserInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function checkAutoLoginToken(ServerRequestInterface $request, SessionInterface $session): ?UserInterface
    {
        $tokenValue = Cookie::get(self::AUTO_LOGIN_COOKIE);

        // No token => no user
        if (!$tokenValue) {
            return null;
        }

        $token = $this->userTokenRepo->findByToken($tokenValue);

        // Incorrect/stale/missing token => no user
        if (!$token) {
            return null;
        }

        // Token expired => delete it, no auto login
        if ($token->isExpired()) {
            $this->userTokenRepo->delete($token);

            return null;
        }

        $userAgent = $this->getUserAgent($request);

        // Invalid user-agent => potential attack => no user
        if (!$token->isValidUserAgent($userAgent)) {
            // Token is invalid or stolen, delete it
            $this->userTokenRepo->delete($token);

            // TODO Notify developers about this potential attack
            return null;
        }

        // Fetch user from valid token
        $user = $token->getUser();

        // Token did its job, remove it
        $this->userTokenRepo->delete($token);

        $this->checkUserIsActive($user);

        // Complete the login with the found data
        $this->completeLogin($user, $session, $request);

        $user->afterAutoLogin();

        // Regenerate token for security purpose
        $this->enableAutoLogin($user, $request);

        // Automatic login was successful
        return $user;
    }

    /**
     * Attempt to log in a user by using an ORM object and plain-text password.
     *
     * @param   string                                 $username Username to log in
     * @param   string                                 $password Password to check against
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return  UserInterface
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\InactiveException
     * @throws \BetaKiller\Auth\IncorrectPasswordException
     * @throws \BetaKiller\Auth\UserDoesNotExistsException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function login(string $username, string $password, ServerRequestInterface $request): UserInterface
    {
        $user = $this->userRepo->searchBy($username);

        if (!$user) {
            throw new UserDoesNotExistsException;
        }

        // Check account is active
        $this->checkUserIsActive($user);

        $loginRole = $this->roleRepo->getLoginRole();

        // No login role => user is not allowed to login
        if (!$user->hasRole($loginRole)) {
            throw new AccessDeniedException;
        }

        // If the passwords match, perform a login
        if (!$this->checkPassword($user, $password)) {
            throw new IncorrectPasswordException;
        }

        $session = $this->getSessionFromRequest($request);

        // Finish the login
        $this->completeLogin($user, $session, $request);

        return $user;
    }

    public function logout(UserInterface $user, bool $dropTokens = null): bool
    {
        $user->beforeSignOut();

        $tokenValue = Cookie::get(self::AUTO_LOGIN_COOKIE);

        if ($tokenValue) {
            // Delete the autologin cookie to prevent re-login
            $this->clearAutoLoginCookie();

            $token = $this->userTokenRepo->findByToken($tokenValue);

            if ($token) {
                // Clear the auto-login token from the database
                $this->userTokenRepo->delete($token);
            }
        }

        if ($dropTokens) {
            // Delete all user tokens
            $this->deleteUserTokens($user);
        }

        $session = $this->getUserSession($user);

        // Destroy the session completely
        $session->clear();
        $user->clearSessionID();

        $this->persistSession($session);
        $this->userRepo->save($user);

        // Double check
        return !$this->isUserLoggedIn($user);
    }

    /**
     * Enable auto-login
     *
     * @param \BetaKiller\Model\UserInterface          $user
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function enableAutoLogin(UserInterface $user, ServerRequestInterface $request): void
    {
        $userAgent = $this->getUserAgent($request);

        // Create a new auto-login token
        $token = $this->generateToken($user, $userAgent);

        // Set the auto-login cookie
        $this->setAutoLoginCookie($token);
    }

    private function checkUserIsActive(UserInterface $user): void
    {
        if (!$user->isActive()) {
            throw new InactiveException();
        }
    }

    private function completeLogin(
        UserInterface $user,
        SessionInterface $session,
        ServerRequestInterface $request
    ): void {
//        // Create new session
//        $session = new \BetaKiller\Session\KohanaSessionAdapter(new Session_Database());

        $user->completeLogin($session);

        // Save session to place session ID to database
        $this->persistSession($session);

        // set session ID in user
        $user->setSessionID($session->getId());
        $this->userRepo->save($user);

        // Store user in session
        $this->setSessionUser($session, $user);

        // Store user-agent
        $userAgent = $this->getUserAgent($request);
        $this->setSessionUserAgent($session, $userAgent);

        $this->setSessionCookie($session);
    }

    private function setSessionUserAgent(SessionInterface $session, string $userAgent): void
    {
        $session->set(self::SESSION_USER_AGENT, $userAgent);
    }

    private function setSessionUser(SessionInterface $session, UserInterface $user): void
    {
        $session->set($this->config->getSessionKey(), $user);

        // Save session
        $this->persistSession($session);
    }

    /**
     * Compare password with original (hashed).
     *
     * @param \BetaKiller\Model\UserInterface $user
     * @param   string                        $password
     *
     * @return  boolean
     */
    private function checkPassword(UserInterface $user, string $password): bool
    {
        if (empty($password)) {
            return false;
        }

        return $this->makePasswordHash($password) === $user->getPassword();
    }

    private function setSessionCookie(SessionInterface $session): void
    {
        // TODO Replace with PST-7 response manipulation
        Cookie::set(self::SESSION_COOKIE, $session->getId(), time() + $this->config->getLifetime());
    }

    private function setAutoLoginCookie(UserToken $token): void
    {
        // TODO Replace with PST-7 response manipulation
        Cookie::set(self::AUTO_LOGIN_COOKIE, $token->getToken(), $token->getExpires() - time());
    }

    private function clearAutoLoginCookie(): void
    {
        // TODO Replace with PST-7 response manipulation
        Cookie::delete(self::AUTO_LOGIN_COOKIE);
    }

    private function deleteUserTokens(UserInterface $user): void
    {
        $this->userTokenRepo->deleteUserTokens($user);
    }

    private function persistSession(SessionInterface $session): void
    {
        // TODO Use original page response after getting PSR-7
        $this->sessionStorage->persistSession($session, new \Zend\Diactoros\Response());
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     * @param string                          $userAgent
     *
     * @return \BetaKiller\Model\UserToken
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Kohana_Exception
     */
    private function generateToken(UserInterface $user, string $userAgent): UserToken
    {
        do {
            $token = sha1(uniqid(\Text::random('alnum', UserToken::TOKEN_LENGTH), true));
        } while ($this->userTokenRepo->findByToken($token));

        $model = new UserToken();

        $model
            ->setToken($token)
            ->setUser($user)
            ->setUserAgent($userAgent)
            ->setExpires(time() + $this->config->getLifetime());

        $this->userTokenRepo->save($model);

        return $model;
    }

    private function getUserAgent(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();

        return $serverParams['HTTP_USER_AGENT'] ?? '';
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
