<?php

declare(strict_types=1);

namespace BetaKiller\Test;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Config\SessionConfigInterface;
use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Factory\UserSessionFactoryInterface;
use BetaKiller\Helper\CookieHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\SessionHelper;
use BetaKiller\Middleware\SessionMiddleware;
use BetaKiller\Model\UserSession;
use BetaKiller\Model\UserSessionInterface;
use BetaKiller\Repository\UserSessionRepositoryInterface;
use BetaKiller\Security\EncryptionInterface;
use BetaKiller\Session\DatabaseSessionStorage;
use BetaKiller\Session\SessionStorageInterface;
use DateTimeImmutable;
use Dflydev\FigCookies\SetCookies;
use Laminas\Diactoros\ServerRequest;
use Middlewares\Utils\RequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DatabaseSessionStorageTest extends AbstractTestCase
{
    private const FAKE_KEY = 'persisted';

    private array $sessionMocks = [];

    private array $sessionContent = [];

    public function testConstructor(): void
    {
        $object = $this->createStorage();

        self::assertInstanceOf(SessionStorageInterface::class, $object);
    }

    public function testSessionStability(): void
    {
        $storage    = $this->createStorage();
        $middleware = new SessionMiddleware($storage);

        $firstId = $secondId = null;

        $firstRequest = $this->prepareRequest(null);

        // Generate session ID
        $firstResp = $middleware->process(
            $firstRequest,
            new RequestHandler(function (ServerRequestInterface $request) use (&$firstId) {
                $session = ServerRequestHelper::getSession($request);

                $firstId = $session->getId();

                return ResponseHelper::html('OK');
            })
        );

        // Check data is saved
        self::assertArrayHasKey($firstId, $this->sessionMocks, 'Session data has not been stored');

        // Fetch session ID
        $firstToken = $this->getResponseSessionToken($firstResp);

        // Preset session ID in request
        $secondReq = $this->prepareRequest($firstToken);

        // Check data and regenerate session ID
        $secondResp = $middleware->process(
            $secondReq,
            new RequestHandler(function (ServerRequestInterface $request) use ($firstId, &$secondId) {
                $session = ServerRequestHelper::getSession($request);

                $secondId = $session->getId();

                self::assertEquals($firstId, $secondId, 'Session ID has not kept (first => second)');

                return ResponseHelper::html('OK');
            })
        );

        // Check data is saved
        self::assertArrayHasKey($secondId, $this->sessionMocks, 'Session data has not been stored');

        $secondToken = $this->getResponseSessionToken($secondResp);

        self::assertEquals($firstToken, $secondToken, 'Session token has changed (first => second)');
    }

    public function testClearSessionOnLogout(): void
    {
        $storage = $this->createStorage();

        $firstRequest = $this->prepareRequest(null);

        $middleware = new SessionMiddleware($storage);

        $firstId = $secondId = $thirdId = $fourthId = null;

        // Generate session ID
        $firstResp = $middleware->process(
            $firstRequest,
            new RequestHandler(function (ServerRequestInterface $request) use (&$firstId) {
                $session = ServerRequestHelper::getSession($request);

                // Fake User ID and random key to be cleared
                $session->set(SessionHelper::AUTH_USER_ID, '1');
                $session->set(self::FAKE_KEY, true);

                $firstId = $session->getId();

                return ResponseHelper::html('OK');
            })
        );

        // Fetch session ID
        $firstToken = $this->getResponseSessionToken($firstResp);

        // Preset session ID in request
        $secondReq = $this->prepareRequest($firstToken);

        // Check data and regenerate session ID
        $secondResp = $middleware->process(
            $secondReq,
            new RequestHandler(function (ServerRequestInterface $request) use ($firstId, &$secondId) {
                $session = ServerRequestHelper::getSession($request);

                $secondId = $session->getId();

                self::assertEquals($firstId, $secondId, 'Session ID has not kept (first => second)');

                self::assertTrue($session->has(SessionHelper::AUTH_USER_ID), 'User ID has not kept (second)');
                self::assertTrue($session->has(self::FAKE_KEY), 'Session data has not kept (second)');

                // Fake logout
                SessionHelper::removeUserID($session);
                $session->regenerate();

                return ResponseHelper::html('OK');
            })
        );

        $secondToken = $this->getResponseSessionToken($secondResp);

        self::assertNotEquals($firstToken, $secondToken, 'Session token has not changed (first => second)');

        // Preset session ID in request
        $thirdReq = $this->prepareRequest($secondToken);

        // Check data
        $thirdResponse = $middleware->process(
            $thirdReq,
            new RequestHandler(function (ServerRequestInterface $request) use ($secondId, &$thirdId) {
                $session = ServerRequestHelper::getSession($request);

                $thirdId = $session->getId();

                self::assertNotEquals($secondId, $thirdId, 'Session ID has not reset (second => third)');

                self::assertFalse($session->has(SessionHelper::AUTH_USER_ID), 'User ID has not been cleared (third)');
                self::assertFalse($session->has(self::FAKE_KEY), 'Session data has not been cleared (third)');

                return ResponseHelper::html('OK');
            })
        );

        $thirdToken = $this->getResponseSessionToken($thirdResponse);

        self::assertEquals($secondToken, $thirdToken, 'Session token has not kept (second => third)');

        // Preset session ID in request
        $fourthReq = $this->prepareRequest($thirdToken);

        // Check data
        $fourthResponse = $middleware->process(
            $fourthReq,
            new RequestHandler(function (ServerRequestInterface $request) use (&$fourthId, $thirdId) {
                $session = ServerRequestHelper::getSession($request);

                $fourthId = $session->getId();

                return ResponseHelper::html('OK');
            })
        );

        $fourthToken = $this->getResponseSessionToken($fourthResponse);

        self::assertEquals($thirdId, $fourthId, 'Session ID has not kept (third => fourth)');
        self::assertEquals($thirdToken, $fourthToken, 'Session token has no kept (third => fourth)');
    }

    public function testKeepSessionOnLogin(): void
    {
        $storage = $this->createStorage();

        $firstRequest = $this->prepareRequest(null);

        $middleware = new SessionMiddleware($storage);

        $firstId = $secondId = $thirdId = $fourthId = null;

        // Generate session ID
        $firstResp = $middleware->process(
            $firstRequest,
            new RequestHandler(function (ServerRequestInterface $request) use (&$firstId) {
                $session = ServerRequestHelper::getSession($request);

                $firstId = $session->getId();

                $session->set(self::FAKE_KEY, true);

                return ResponseHelper::html('OK');
            })
        );

        // Fetch session ID
        $firstToken = $this->getResponseSessionToken($firstResp);

        // Preset session ID in request
        $secondReq = $this->prepareRequest($firstToken);

        // Check data and regenerate session ID
        $secondResp = $middleware->process(
            $secondReq,
            new RequestHandler(function (ServerRequestInterface $request) use ($firstId, &$secondId) {
                $session = ServerRequestHelper::getSession($request);

                $secondId = $session->getId();

                self::assertEquals($firstId, $secondId, 'Session ID has not kept (first => second)');

                // Fake random key to be kept
                self::assertTrue($session->has(self::FAKE_KEY), 'Session data has not kept (second)');

                // Fake login
                $session->set(SessionHelper::AUTH_USER_ID, '1');
                $session->regenerate();

                return ResponseHelper::html('OK');
            })
        );

        $secondToken = $this->getResponseSessionToken($secondResp);

        self::assertNotEquals($firstToken, $secondToken, 'Session token has not changed (first => second)');

        // Preset session ID in request
        $thirdReq = $this->prepareRequest($secondToken);

        // Check data
        $thirdResponse = $middleware->process(
            $thirdReq,
            new RequestHandler(function (ServerRequestInterface $request) use ($secondId, &$thirdId) {
                $session = ServerRequestHelper::getSession($request);

                $thirdId = $session->getId();

                self::assertNotEquals($secondId, $thirdId, 'Session ID has not reset (second => third)');

                self::assertTrue($session->has(SessionHelper::AUTH_USER_ID), 'User ID has not been kept (third)');
                self::assertTrue($session->has(self::FAKE_KEY), 'Session data has not been kept (third)');

                return ResponseHelper::html('OK');
            })
        );

        $thirdToken = $this->getResponseSessionToken($thirdResponse);

        self::assertEquals($secondToken, $thirdToken, 'Session token has not kept (second => third)');

        // Preset session ID in request
        $fourthReq = $this->prepareRequest($thirdToken);

        // Check data
        $fourthResponse = $middleware->process(
            $fourthReq,
            new RequestHandler(function (ServerRequestInterface $request) use (&$fourthId, $thirdId) {
                $session = ServerRequestHelper::getSession($request);

                $fourthId = $session->getId();

                return ResponseHelper::html('OK');
            })
        );

        $fourthToken = $this->getResponseSessionToken($fourthResponse);

        self::assertEquals($thirdId, $fourthId, 'Session ID has not kept (third => fourth)');
        self::assertEquals($thirdToken, $fourthToken, 'Session token has no kept (third => fourth)');
    }

    public function testTransitionOnUserAgentChange(): void
    {
        /* Create new session with default User-Agent */

        $storage = $this->createStorage();

        $firstRequest = $this->prepareRequest(null);

        $middleware = new SessionMiddleware($storage);

        $firstId = $secondId = null;

        // Generate session ID
        $firstResp = $middleware->process(
            $firstRequest,
            new RequestHandler(function (ServerRequestInterface $request) use (&$firstId) {
                $session = ServerRequestHelper::getSession($request);

                $firstId = $session->getId();

                $session->set(self::FAKE_KEY, true);

                return ResponseHelper::html('OK');
            })
        );

        // Fetch session ID
        $firstToken = $this->getResponseSessionToken($firstResp);

        /* Make request with another User-Agent */

        // Preset session ID in request
        $secondReq = $this->prepareRequest($firstToken, 'Another User-Agent');

        // Trigger session ID change
        $secondResp = $middleware->process(
            $secondReq,
            new RequestHandler(function (ServerRequestInterface $request) use ($firstId, &$secondId) {
                $session = ServerRequestHelper::getSession($request);

                $secondId = $session->getId();

                self::assertNotEquals($firstId, $secondId, 'Session ID was kept (first => second)');

                self::assertFalse($session->has(self::FAKE_KEY), 'Session data was kept (second)');

                return ResponseHelper::html('OK');
            })
        );

        $secondToken = $this->getResponseSessionToken($secondResp);

        // Check Session ID is changed
        self::assertNotEquals($firstToken, $secondToken, 'Session token has not changed (first => second)');
    }

    private function createStorage(): DatabaseSessionStorage
    {
        $appConfig      = $this->createMock(AppConfigInterface::class);
        $appEnv         = $this->createMock(AppEnvInterface::class);
        $sessionFactory = $this->createMock(UserSessionFactoryInterface::class);
        $sessionRepo    = $this->createMock(UserSessionRepositoryInterface::class);
        $sessionConfig  = $this->createMock(SessionConfigInterface::class);
        $encryption     = $this->createMock(EncryptionInterface::class);

        // Retrieve data
        $sessionRepo->method('findByToken')->willReturnCallback(function (string $token): ?UserSessionInterface {
//            d('retrieve', $token);

            if (!isset($this->sessionMocks[$token])) {
                return null;
            }

            return $this->sessionMocks[$token];
        });

        // Store data
        $sessionFactory->method('create')->willReturnCallback(function (string $id): UserSessionInterface {
            $model = $this->createMock(UserSession::class);

            $model->method('getToken')->willReturn($id);
            $model->method('getCreatedAt')->willReturn(new DateTimeImmutable());

            $model->method('setContents')->willReturnCallback(function (string $value) use ($model, $id): UserSessionInterface {
                $this->sessionContent[$id] = $value;

                return $model;
            });

            $model->method('getContents')->willReturnCallback(function () use ($id): string {
                return $this->sessionContent[$id];
            });

            $this->sessionMocks[$id] = $model;

            return $model;
        });

        $sessionConfig->method('getLifetime')->willReturn(new \DateInterval('P1W'));

        $cookies = new CookieHelper($appConfig, $appEnv);

        return new DatabaseSessionStorage($sessionFactory, $sessionRepo, $sessionConfig, $encryption, $cookies);
    }

    private function prepareRequest(?string $sid, string $userAgent = null): ServerRequestInterface
    {
        $serverParams = [
            'HTTP_USER_AGENT' => $userAgent ?? 'Default User-Agent',
        ];

        $req = new ServerRequest($serverParams);

        if ($sid) {
            $req = $req->withCookieParams([
                DatabaseSessionStorage::COOKIE_NAME => $sid,
            ]);
        }

        return $req;
    }

    private function getResponseSessionToken(ResponseInterface $response): string
    {
        return SetCookies::fromResponse($response)->get(DatabaseSessionStorage::COOKIE_NAME)->getValue();
    }
}
