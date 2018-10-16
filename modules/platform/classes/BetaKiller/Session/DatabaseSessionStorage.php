<?php
declare(strict_types=1);

namespace BetaKiller\Session;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Auth\SessionConfig;
use BetaKiller\Exception;
use BetaKiller\Exception\DomainException;
use BetaKiller\Exception\SecurityException;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\ResponseHelper;
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
use Zend\Expressive\Session\Session;
use Zend\Expressive\Session\SessionIdentifierAwareInterface;
use Zend\Expressive\Session\SessionInterface;

class DatabaseSessionStorage implements SessionStorageInterface
{
    use LoggerHelperTrait;

    public const COOKIE_NAME      = 'sid';
    public const COOKIE_DELIMITER = '~';

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
     * DatabaseSessionStorage constructor.
     *
     * @param \BetaKiller\Repository\UserSessionRepository $sessionRepo
     * @param \BetaKiller\Auth\SessionConfig               $config
     * @param \Psr\Log\LoggerInterface                     $logger
     */
    public function __construct(
        UserSessionRepository $sessionRepo,
        SessionConfig $config,
        Encryption $encryption,
        LoggerInterface $logger
    ) {
        $this->sessionRepo = $sessionRepo;
        $this->config      = $config;
        $this->logger      = $logger;
        $this->encryption = $encryption;
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
        $ipAddress = ServerRequestHelper::getIpAddress($request);
        $cookie    = ServerRequestHelper::getCookie($request, self::COOKIE_NAME);

        if (!$cookie) {
            // No session (cleared by browser or new visitor) => regenerate empty session
            return $this->createSession($userAgent, $ipAddress);
        }

        $parts = explode(self::COOKIE_DELIMITER, $cookie, 2);
        $token = \array_pop($parts);

        if (!$token) {
            throw new Exception('Invalid session cookie format ":value"', [':value' => $cookie]);
        }

        $model = $this->sessionRepo->findByToken($token);

        if (!$model) {
            // Missing session (cleared by gc or stale) => regenerate empty session
            return $this->createSession($userAgent, $ipAddress);
        }

        if ($this->isExpired($model)) {
            // Session exists, but expired => create empty
            return $this->createSession($userAgent, $ipAddress);
        }

        $session = $this->restoreSession($model);

        $this->checkUserAgent($session, $userAgent);

        return $session;
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

    private function checkUserAgent(SessionInterface $session, string $userAgent): void
    {
        if (!$session instanceof SessionIdentifierAwareInterface) {
            throw new DomainException('Session must implement :interface', [
                ':interface' => SessionIdentifierAwareInterface::class,
            ]);
        }

        // Check session is valid (only user agent check coz IP address may be changed on mobile connection)
        if (SessionHelper::getUserAgent($session) === $userAgent) {
            return;
        }

        // Warn about potential attack and restrict access
        $this->logException(
            $this->logger,
            new SecurityException('User agent juggling for session :token with :agent', [
                ':token' => $session->getId(),
                ':agent' => $userAgent,
            ])
        );

        throw new AccessDeniedException();
    }

    private function restoreSession(UserSession $model): SessionInterface
    {
        $content = $model->getContents();

        // Decode session data
        $data = $this->decodeData($content);

        // Create session DTO
        $session = $this->sessionFactory($model->getToken(), $data);

        // Restore user in session if exists
        $user = $model->getUser();

        if ($user) {
            SessionHelper::setUserID($session, $user);
        }

        // Valid session => return it
        return $session;
    }

    /**
     * @param string $id
     *
     * @return \Zend\Expressive\Session\Session
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByToken(string $id): SessionInterface
    {
        $model = $this->sessionRepo->findByToken($id);

        if (!$model) {
            throw new SecurityException('Trying to retrieve missing session :token', [
                ':token' => $id,
            ]);
        }

        if ($this->isExpired($model)) {
            throw new SecurityException('Trying to retrieve expired session :token', [
                ':token' => $id,
            ]);
        }

        // Skip user agent check cos it can not be performed here
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

        // Import user ID from Session only once
        if ($userID && !$model->hasUser()) {
            $model->setUserID($userID);
        }

        // Encode and encrypt session data
        $content = $this->encodeData($session->toArray());

        // Update model data from session
        $model
            ->setContents($content)
            ->setLastActiveAt(new DateTimeImmutable);

        $this->sessionRepo->save($model);

        // Set cookie
        return ResponseHelper::setCookie(
            $response,
            self::COOKIE_NAME,
            $session->getId(),
            $this->config->getLifetime()
        );
    }

    private function sessionFactory(string $token, array $data): SessionInterface
    {
        return new Session($data, $token);
    }

    private function createSession(string $userAgent, string $ipAddress, array $data = null): SessionInterface
    {
        // Generate new token and fresh session object without data
        $session = $this->sessionFactory($this->generateToken(), $data ?? []);

        SessionHelper::setUserAgent($session, $userAgent);
        SessionHelper::setIpAddress($session, $ipAddress);

        return $session;
    }

    private function regenerateSession(SessionInterface $session): SessionInterface
    {
        // Delete session record if exists
        $model = $this->getSessionModel($session);

        if ($model) {
            $this->sessionRepo->delete($model);
        }

        // Generate new token and create fresh session with empty data
        return $this->createSession(
            SessionHelper::getUserAgent($session),
            SessionHelper::getIpAddress($session)
        );
    }

    private function generateToken(): string
    {
        do {
            $token = sha1(uniqid(\Text::random('alnum', UserSession::TOKEN_LENGTH), true));
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

        $model
            ->setToken($session->getId())
            ->setCreatedAt(new DateTimeImmutable);

        return $model;
    }

    private function encodeData(array $data): string
    {
        $content = \serialize($data);

        // Encrypt
        $content = $this->encryption->encrypt($content, $this->getEncryptionKey());

        return $content;
    }

    private function decodeData(string $content): array
    {
//        $content = \base64_decode($encodedContent);
//
//        if (!$content) {
//            throw new Exception('Invalid session content: :value', [':value' => $encodedContent]);
//        }

        // Decrypt
        $content = $this->encryption->decrypt($content, $this->getEncryptionKey());

        $data = \unserialize($content, $this->config->getAllowedClassNames());

        if (!\is_array($data)) {
            throw new Exception('Invalid session data: :value', [':value' => \json_encode($data)]);
        }

        return $data;
    }

    private function getEncryptionKey(): string
    {
        return $this->config->getEncryptionKey();
    }
}
