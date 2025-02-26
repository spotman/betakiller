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
use Random\Engine\Secure;
use Random\Randomizer;

final readonly class DatabaseSessionStorage implements SessionStorageInterface
{
    public const COOKIE_NAME = 'sid';

    /**
     * DatabaseSessionStorage constructor.
     *
     * @param \BetaKiller\Repository\UserSessionRepositoryInterface $sessionRepo
     * @param \BetaKiller\Config\SessionConfigInterface             $config
     * @param \BetaKiller\Security\EncryptionInterface              $encryption
     * @param \BetaKiller\Helper\CookieHelper                       $cookieHelper
     */
    public function __construct(
        private UserSessionRepositoryInterface $sessionRepo,
        private SessionConfigInterface $config,
        private EncryptionInterface $encryption,
        private CookieHelper $cookieHelper
    ) {
    }

    /**
     * @inheritDoc
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
        $userAgent = ServerRequestHelper::getUserAgent($request);

        $cookies = $request->getCookieParams();
        $token   = $cookies[self::COOKIE_NAME] ?? null;

        // Bots, fake requests, etc => reuse empty session
        // No session (cleared by browser or new visitor) => regenerate empty session
        if (!$token || !$userAgent) {
            return $this->createSession($token);
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
        $session = $this->createSession($model->getToken(), $data);

        // Restore user in session if exists
        if ($model->hasUser()) {
            SessionHelper::setUserID($session, $model->getUser());
        }

        // Valid session => return it
        return $session;
    }

    /**
     * @inheritDoc
     */
    public function getByToken(string $token): SessionInterface
    {
        $model = $this->sessionRepo->findByToken($token);

        if (!$model) {
            // Missing session (cleared by gc or stale) => regenerate empty session
            return $this->createSession($token);
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
     * @inheritDoc
     */
    public function getUserSessions(UserInterface $user): array
    {
        $sessionModels = $this->sessionRepo->getUserSessions($user);

        return array_map(fn(UserSessionInterface $model) => $this->restoreSession($model), $sessionModels);
    }

    /**
     * @inheritDoc
     */
    public function createSession(?string $id = null, array $data = null): SessionInterface
    {
        // Generate new token and fresh session object without data
        return new Session($data ?? [], $id ?? $this->generateToken());
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function destroySession(SessionInterface $session): void
    {
        // Delete session record if exists
        $model = $this->getSessionModel($session);

        if ($model) {
            $this->sessionRepo->delete($model);
        }
    }

    private function storeSession(SessionInterface $session): void
    {
        // Do not store empty sessions
        if (SessionHelper::isEmpty($session)) {
            return;
        }

        // Fetch session model if exists
        // Create new model for provided token
        $model = $this->getSessionModel($session) ?? $this->createSessionModel($session);

        // Import user ID from Session only once
        if (SessionHelper::hasUserID($session) && !$model->hasUser()) {
            $userID = SessionHelper::getUserID($session);
            $model->setUserID($userID);
        }

        if ($session->isRegenerated()) {
            $model->markAsRegenerated();
        }

        // Encode and encrypt session data
        $content = $this->encodeData($session->toArray());

        // Update model data from session
        $model
            ->setContents($content)
            ->setLastActiveAt(new DateTimeImmutable());

        $this->sessionRepo->save($model);
    }

    private function regenerateSession(SessionInterface $oldSession): SessionInterface
    {
        // Generate new token and create fresh session with empty data
        $newSession = $this->createSession();

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
        $rand = new Randomizer(new Secure());

        return bin2hex($rand->getBytes(UserSession::TOKEN_LENGTH / 2));
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

        $model
            ->setToken($session->getId())
            ->setCreatedAt(new DateTimeImmutable());

        return $model;
    }

    private function encodeData(array $data): string
    {
        $content = json_encode($data, JSON_OBJECT_AS_ARRAY);

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

        $data = json_decode($content, true);

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
