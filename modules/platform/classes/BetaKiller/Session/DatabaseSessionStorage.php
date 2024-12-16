<?php
declare(strict_types=1);

namespace BetaKiller\Session;

use BetaKiller\Config\SessionConfigInterface;
use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Exception;
use BetaKiller\Exception\DomainException;
use BetaKiller\Helper\CookieHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Model\UserSession;
use BetaKiller\Model\UserSessionInterface;
use BetaKiller\Repository\UserSessionRepositoryInterface;
use BetaKiller\Security\EncryptionInterface;
use DateTimeImmutable;
use Mezzio\Session\Session;
use Mezzio\Session\SessionIdentifierAwareInterface;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\UuidInterface;
use Text;

class DatabaseSessionStorage implements SessionStorageInterface
{
    public const COOKIE_NAME = 'sid';

    /**
     * @var \BetaKiller\Config\SessionConfigInterface
     */
    private SessionConfigInterface $config;

    /**
     * @var \BetaKiller\Repository\UserSessionRepositoryInterface
     */
    private UserSessionRepositoryInterface $sessionRepo;

    /**
     * @var \BetaKiller\Security\EncryptionInterface
     */
    private EncryptionInterface $encryption;

    /**
     * @var \BetaKiller\Helper\CookieHelper
     */
    private CookieHelper $cookieHelper;

    /**
     * DatabaseSessionStorage constructor.
     *
     * @param \BetaKiller\Repository\UserSessionRepositoryInterface $sessionRepo
     * @param \BetaKiller\Config\SessionConfigInterface             $config
     * @param \BetaKiller\Security\EncryptionInterface              $encryption
     * @param \BetaKiller\Helper\CookieHelper                       $cookieHelper
     */
    public function __construct(
        UserSessionRepositoryInterface $sessionRepo,
        SessionConfigInterface         $config,
        EncryptionInterface            $encryption,
        CookieHelper                   $cookieHelper
    ) {
        $this->sessionRepo  = $sessionRepo;
        $this->config       = $config;
        $this->encryption   = $encryption;
        $this->cookieHelper = $cookieHelper;
    }

    /**
     * Generate a session data instance based on the request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Mezzio\Session\SessionInterface
     */
    public function initializeSessionFromRequest(ServerRequestInterface $request): SessionInterface
    {
        $p = RequestProfiler::begin($request, 'Initialize session from request');

        $session = $this->fetchSessionFromRequest($request);

        RequestProfiler::end($p);

        return $session;
    }

    private function fetchSessionFromRequest(ServerRequestInterface $request): SessionInterface
    {
        $userAgent  = ServerRequestHelper::getUserAgent($request);
        $originUrl  = ServerRequestHelper::getUrl($request);
        $originUuid = ServerRequestHelper::getRequestUuid($request);

        if (!$userAgent) {
            // Bots, fake requests, etc => regenerate empty session
            return $this->createSession($originUrl, $originUuid);
        }

        $cookies = $request->getCookieParams();
        $token   = $cookies[self::COOKIE_NAME] ?? null;

        if (!$token) {
            // No session (cleared by browser or new visitor) => regenerate empty session
            return $this->createSession($originUrl, $originUuid);
        }

        return $this->getByToken($token);
    }

    private function isExpired(UserSessionInterface $model): bool
    {
        $expireInterval = $this->config->getLifetime();

        if ($model->isExpiredIn($expireInterval)) {
            // Session exists, but expired => delete it
            $this->sessionRepo->delete($model);

            return true;
        }

        return false;
    }

    private function restoreSession(UserSessionInterface $model): SessionInterface
    {
        $content = $model->getContents();

        // Decode session data
        $data = $this->decodeData($content);

        // Create session DTO
        $session = $this->sessionFactory($model->getToken(), $data);

        SessionHelper::setCreatedAt($session, $model->getCreatedAt());
        SessionHelper::markAsPersistent($session);

        // Restore user in session if exists
        if ($model->hasUser()) {
            SessionHelper::setUserID($session, $model->getUser());
        }

        // Valid session => return it
        return $session;
    }

    /**
     * @param string $token
     *
     * @return \Mezzio\Session\Session
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByToken(string $token): SessionInterface
    {
        $model = $this->sessionRepo->findByToken($token);

        if (!$model) {
            // Missing session (cleared by gc or stale) => regenerate empty session
            return $this->createSession();
        }

        if ($model->isRegenerated()) {
            // Session exists, but was regenerated => create empty (for security purpose)
            return $this->createSession();
        }

        if ($this->isExpired($model)) {
            // Session exists, but expired => create empty
            return $this->createSession();
        }

        // No user agent / IP checks coz of annoying session issues
        // They are constantly changing after browser update + inconsistent behaviour
        return $this->restoreSession($model);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \Mezzio\Session\SessionInterface[]
     * @throws \BetaKiller\Exception\DomainException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUserSessions(UserInterface $user): array
    {
        $sessionModels = $this->sessionRepo->getUserSessions($user);

        return array_map(fn(UserSessionInterface $model) => $this->restoreSession($model), $sessionModels);
    }

    /**
     * @param string|null                     $originUrl
     * @param \Ramsey\Uuid\UuidInterface|null $originUuid
     *
     * @return \Mezzio\Session\SessionInterface
     */
    public function createSession(string $originUrl = null, UuidInterface $originUuid = null): SessionInterface
    {
        // Generate new token and fresh session object without data
        $session = $this->sessionFactory($this->generateToken(), []);

        if ($originUrl) {
            SessionHelper::setOriginUrl($session, $originUrl);
        }

        if ($originUuid) {
            SessionHelper::setOriginUuid($session, $originUuid);
        }

        SessionHelper::setCreatedAt($session, new DateTimeImmutable);

        return $session;
    }

    /**
     * Persist the session data instance.
     *
     * Persists the session data, returning a response instance with any
     * artifacts required to return to the client.
     *
     * @param \Mezzio\Session\SessionInterface    $session
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function persistSession(SessionInterface $session, ResponseInterface $response): ResponseInterface
    {
        if (!$session instanceof SessionIdentifierAwareInterface) {
            throw new DomainException('Session must implement :interface', [
                ':interface' => SessionIdentifierAwareInterface::class,
            ]);
        }

        // Keep session
        $this->storeSession($session);

        if ($session->isRegenerated()) {
            // Generate new token and create fresh session with original user agent and IP address
            $session = $this->regenerateSession($session);

            // Keep new session
            $this->storeSession($session);
        }

        // Set cookie
        return $this->cookieHelper->set(
            $response,
            self::COOKIE_NAME,
            $session->getId(),
            $this->config->getLifetime()
        );
    }

    /**
     * @param \Mezzio\Session\SessionInterface $session
     */
    public function destroySession(SessionInterface $session): void
    {
        // Delete session record if exists
        $model = $this->getSessionModel($session);

        if ($model) {
            $this->sessionRepo->delete($model);
        }
    }

    private function sessionFactory(string $token, array $data): SessionInterface
    {
        return new Session($data, $token);
    }

    private function storeSession(SessionInterface $session): void
    {
        // Fetch session model if exists
        $model = $this->getSessionModel($session);

        if (!$model) {
            // Create new model for provided token
            $model = $this->createSessionModel($session);
        }

        $origin = SessionHelper::getOriginUrl($session);

        // Import user ID from Session only once
        if (SessionHelper::hasUserID($session) && !$model->hasUser()) {
            $userID = SessionHelper::getUserID($session);
            $model->setUserID($userID);
        }

        // Import origin URL if exists
        if ($origin) {
            $model->setOrigin($origin);
        }

        if ($session->isRegenerated()) {
            $model->markAsRegenerated();
        }

        SessionHelper::markAsPersistent($session);

        // Encode and encrypt session data
        $content = $this->encodeData($session->toArray());

        // Update model data from session
        $model
            ->setContents($content)
            ->setLastActiveAt(new DateTimeImmutable);

        $this->sessionRepo->save($model);
    }

    private function regenerateSession(SessionInterface $oldSession): SessionInterface
    {
        // Generate new token and create fresh session with empty data
        $newSession = $this->createSession(
            SessionHelper::getOriginUrl($oldSession),
            SessionHelper::getOriginUuid($oldSession)
        );

        $userID = SessionHelper::getUserID($oldSession);

        if ($userID) {
            // Copy data from old session on login to allow flash messages and other markers to be saved
            // Clear session data on logout
            SessionHelper::transferData($oldSession, $newSession);
        }

        return $newSession;
    }

    private function generateToken(): string
    {
        do {
            $token = sha1(uniqid(Text::random('alnum', UserSession::TOKEN_LENGTH), true));
        } while ($this->sessionRepo->findByToken($token));

        return $token;
    }

    private function getSessionModel(SessionInterface $session): ?UserSessionInterface
    {
        if (!$session instanceof SessionIdentifierAwareInterface) {
            throw new DomainException('Session must implement :interface', [
                ':interface' => SessionIdentifierAwareInterface::class,
            ]);
        }

        return $this->sessionRepo->findByToken($session->getId());
    }

    private function createSessionModel(SessionInterface $session): UserSessionInterface
    {
        if (!$session instanceof SessionIdentifierAwareInterface) {
            throw new DomainException('Session must implement :interface', [
                ':interface' => SessionIdentifierAwareInterface::class,
            ]);
        }

        $model = new UserSession();

        // Fetch original creation time
        $createdAt = SessionHelper::getCreatedAt($session);

        $model
            ->setToken($session->getId())
            ->setCreatedAt($createdAt);

        return $model;
    }

    private function encodeData(array $data): string
    {
        $content = serialize($data);

        $key = $this->getEncryptionKey();

        if ($key) {
            // Encrypt
            $content = $this->encryption->encrypt($content, $key);
        }

        return $content;
    }

    private function decodeData(string $content): array
    {
        $key = $this->getEncryptionKey();

        if ($key) {
            // Decrypt
            $content = $this->encryption->decrypt($content, $key);
        }

        $data = unserialize($content, $this->config->getAllowedClassNames());

        if (!is_array($data)) {
            throw new Exception('Invalid session data: :value', [':value' => json_encode($data)]);
        }

        return $data;
    }

    private function getEncryptionKey(): ?string
    {
        return $this->config->getEncryptionKey();
    }
}
