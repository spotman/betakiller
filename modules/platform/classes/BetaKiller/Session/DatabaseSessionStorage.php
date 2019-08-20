<?php
declare(strict_types=1);

namespace BetaKiller\Session;

use BetaKiller\Auth\SessionConfig;
use BetaKiller\Exception;
use BetaKiller\Exception\DomainException;
use BetaKiller\Helper\CookieHelper;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Model\UserSession;
use BetaKiller\Repository\UserSessionRepository;
use BetaKiller\Security\Encryption;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Text;
use Zend\Expressive\Session\Session;
use Zend\Expressive\Session\SessionIdentifierAwareInterface;
use Zend\Expressive\Session\SessionInterface;

class DatabaseSessionStorage implements SessionStorageInterface
{
    use LoggerHelperTrait;

    public const COOKIE_NAME = 'sid';

    /**
     * @var \BetaKiller\Auth\SessionConfig
     */
    private $config;

    /**
     * @var \BetaKiller\Repository\UserSessionRepository
     */
    private $sessionRepo;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Security\Encryption
     */
    private $encryption;

    /**
     * @var \BetaKiller\Helper\CookieHelper
     */
    private $cookies;

    /**
     * DatabaseSessionStorage constructor.
     *
     * @param \BetaKiller\Repository\UserSessionRepository $sessionRepo
     * @param \BetaKiller\Auth\SessionConfig               $config
     * @param \BetaKiller\Security\Encryption              $encryption
     * @param \BetaKiller\Helper\CookieHelper              $cookies
     * @param \Psr\Log\LoggerInterface                     $logger
     */
    public function __construct(
        UserSessionRepository $sessionRepo,
        SessionConfig $config,
        Encryption $encryption,
        CookieHelper $cookies,
        LoggerInterface $logger
    ) {
        $this->sessionRepo = $sessionRepo;
        $this->config      = $config;
        $this->logger      = $logger;
        $this->encryption  = $encryption;
        $this->cookies     = $cookies;
    }

    /**
     * Generate a session data instance based on the request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Zend\Expressive\Session\SessionInterface
     */
    public function initializeSessionFromRequest(ServerRequestInterface $request): SessionInterface
    {
        $userAgent = ServerRequestHelper::getUserAgent($request);
        $originUrl = ServerRequestHelper::getUrl($request);

        if (!$userAgent) {
            // Bots, fake requests, etc => regenerate empty session
            return $this->createSession($originUrl);
        }

        $token = $this->cookies->get($request, self::COOKIE_NAME);

        if (!$token) {
            // No session (cleared by browser or new visitor) => regenerate empty session
            return $this->createSession($originUrl);
        }

        return $this->getByToken($token);
    }

    private function isExpired(UserSession $model): bool
    {
        $expireInterval = $this->config->getLifetime();

        if ($model->isExpiredIn($expireInterval)) {
            // Session exists, but expired => delete it
            $this->sessionRepo->delete($model);

            return true;
        }

        return false;
    }

    private function restoreSession(UserSession $model): SessionInterface
    {
        $content = $model->getContents();

        // Decode session data
        $data = $this->decodeData($content);

        // Create session DTO
        $session = $this->sessionFactory($model->getToken(), $data);

        SessionHelper::setCreatedAt($session, $model->getCreatedAt());
        SessionHelper::markAsPersistent($session);

        // Restore user in session if exists
        $user = $model->getUser();

        if ($user) {
            SessionHelper::setUserID($session, $user);
        }

        // Valid session => return it
        return $session;
    }

    /**
     * @param string $token
     *
     * @return \Zend\Expressive\Session\Session
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByToken(string $token): SessionInterface
    {
        $model = $this->sessionRepo->findByToken($token);

        if (!$model) {
            // Missing session (cleared by gc or stale) => regenerate empty session
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
     * @return \Zend\Expressive\Session\SessionInterface[]
     * @throws \BetaKiller\Exception\DomainException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUserSessions(UserInterface $user): array
    {
        return $this->sessionRepo->getUserSessions($user);
    }

    /**
     * @param string $originUrl
     *
     * @return \Zend\Expressive\Session\SessionInterface
     */
    public function createSession(string $originUrl = null): SessionInterface
    {
        // Generate new token and fresh session object without data
        $session = $this->sessionFactory($this->generateToken(), []);

        if ($originUrl) {
            SessionHelper::setOriginUrl($session, $originUrl);
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
     * @param \Zend\Expressive\Session\SessionInterface $session
     * @param \Psr\Http\Message\ResponseInterface       $response
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

        if ($session->isRegenerated()) {
            // Generate new token and create fresh session with original user agent and IP address
            $session = $this->regenerateSession($session);
        }

        // Fetch session model if exists
        $model = $this->getSessionModel($session);

        if (!$model) {
            // Create new model for provided token
            $model = $this->createSessionModel($session);
        }

        $userID = SessionHelper::getUserID($session);
        $origin = SessionHelper::getOriginUrl($session);

        // Import user ID from Session only once
        if ($userID && !$model->hasUser()) {
            $model->setUserID($userID);
        }

        // Import origin URL if exists
        if ($origin) {
            $model->setOrigin($origin);
        }

        // Encode and encrypt session data
        $content = $this->encodeData($session->toArray());

        // Update model data from session
        $model
            ->setContents($content)
            ->setLastActiveAt(new DateTimeImmutable);

        $this->sessionRepo->save($model);

        SessionHelper::markAsPersistent($session);

        // Set cookie
        return $this->cookies->set(
            $response,
            self::COOKIE_NAME,
            $session->getId(),
            $this->config->getLifetime()
        );
    }

    /**
     * @param \Zend\Expressive\Session\SessionInterface $session
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

    private function regenerateSession(SessionInterface $oldSession): SessionInterface
    {
        // Generate new token and create fresh session with empty data
        $newSession = $this->createSession(
            SessionHelper::getOriginUrl($oldSession)
        );

        $userID = SessionHelper::getUserID($oldSession);

        if ($userID) {
            // Copy data from old session if user is authorized
            SessionHelper::transferData($oldSession, $newSession);
        }

        $this->destroySession($oldSession);

        return $newSession;
    }

    private function generateToken(): string
    {
        do {
            $token = sha1(uniqid(Text::random('alnum', UserSession::TOKEN_LENGTH), true));
        } while ($this->sessionRepo->findByToken($token));

        return $token;
    }

    private function getSessionModel(SessionInterface $session): ?UserSession
    {
        if (!$session instanceof SessionIdentifierAwareInterface) {
            throw new DomainException('Session must implement :interface', [
                ':interface' => SessionIdentifierAwareInterface::class,
            ]);
        }

        return $this->sessionRepo->findByToken($session->getId());
    }

    private function createSessionModel(SessionInterface $session): UserSession
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

        // Encrypt
        $content = $this->encryption->encrypt($content, $this->getEncryptionKey());

        return $content;
    }

    private function decodeData(string $content): array
    {
        // Decrypt
        $content = $this->encryption->decrypt($content, $this->getEncryptionKey());

        $data = unserialize($content, $this->config->getAllowedClassNames());

        if (!is_array($data)) {
            throw new Exception('Invalid session data: :value', [':value' => json_encode($data)]);
        }

        return $data;
    }

    private function getEncryptionKey(): string
    {
        return $this->config->getEncryptionKey();
    }
}
