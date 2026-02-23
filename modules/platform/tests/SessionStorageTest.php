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
use BetaKiller\Session\SessionStorage;
use BetaKiller\Session\SessionStorageInterface;
use DateInterval;
use DateTimeImmutable;
use Dflydev\FigCookies\SetCookies;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Session\SessionInterface;
use Middlewares\Utils\RequestHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

final class SessionStorageTest extends AbstractTestCase
{
    private const TEST_KEY   = 'test';
    private const TEST_VALUE = true;
    private const USER_ID    = '1';

    private array $sessionMocks = [];

    private array $sessionContent = [];

    private MiddlewareInterface $middleware;

    protected function setUp(): void
    {
        $this->middleware = new SessionMiddleware($this->createStorage());
    }

    protected function tearDown(): void
    {
        unset($this->middleware);
    }

    public function testConstructor(): void
    {
        $object = $this->createStorage();

        self::assertInstanceOf(SessionStorageInterface::class, $object);
    }

    public function testEmptySession(): void
    {
        $firstRequest  = $this->createRequest();
        $firstResponse = $this->processRequest($firstRequest);

        $secondRequest  = $this->createRequestFromResponse($firstResponse);
        $secondResponse = $this->processRequest($secondRequest);

        $this->checkTokenKept($firstResponse, $secondResponse);
    }

    public function testSessionStability(): void
    {
        $firstId = $secondId = null;

        $firstRequest = $this->createRequest();

        // Generate starter session
        $firstResp = $this->processRequest($firstRequest, function (SessionInterface $session) use (&$firstId) {
            $firstId = $session->getId();

            $this->setTestData($session);
        });

        // Fetch session ID
        $firstToken = $this->getTokenFromResponse($firstResp);

        // Check Cookie token is equal to Session ID
        self::assertEquals($firstToken, $firstId, 'Token mismatch');

        // Check data stored is created
        self::assertArrayHasKey($firstToken, $this->sessionMocks, 'Session storage was not initialized');

        // Check Session data is stored
        $this->checkStorageData($firstToken, self::TEST_KEY, self::TEST_VALUE);

        // Preset session ID in request
        $secondReq = $this->createRequestFromResponse($firstResp);

        // Check data and regenerate session ID
        $secondResp = $this->processRequest($secondReq, function (SessionInterface $session) use ($firstId, &$secondId) {
            $secondId = $session->getId();

            self::assertEquals($firstId, $secondId, 'Session ID has not kept (first => second)');

            $this->checkTestDataExists($session);
        });

        $secondToken = $this->getTokenFromResponse($secondResp);

        // Check Session ID kept the same
        self::assertEquals($firstToken, $secondToken, 'Session token has changed (first => second)');

        // Check Cookie token is equal to Session ID
        self::assertEquals($secondToken, $secondId, 'Token mismatch');

        // Check data is saved
        self::assertArrayHasKey($secondToken, $this->sessionMocks, 'Session data has not been persisted');

        // Check Session data is stored
        $this->checkStorageData($secondToken, self::TEST_KEY, self::TEST_VALUE);

        // Check stability
        $thirdReq  = $this->createRequestFromResponse($secondResp);
        $thirdResp = $this->processRequest($thirdReq, function (SessionInterface $session) use ($firstId) {
            self::assertEquals($firstId, $session->getId(), 'Session ID has not kept (first => second)');

            $this->checkTestDataExists($session);
        });

        $thirdToken = $this->getTokenFromResponse($thirdResp);

        // Check Session ID kept the same
        self::assertEquals($firstToken, $thirdToken, 'Session token has changed (second => third)');

        // Check Session data is stored
        $this->checkStorageData($firstToken, self::TEST_KEY, self::TEST_VALUE);

        // Check data persisted
        $fourthReq      = $this->createRequestFromResponse($thirdResp);
        $fourthResponse = $this->processRequest($fourthReq);
        $fourthToken    = $this->getTokenFromResponse($fourthResponse);

        self::assertEquals($thirdToken, $fourthToken, 'Session token has changed (third => fourth)');
    }

    public function testKeepDataOnLogin(): void
    {
        // Create starter session
        $firstRequest  = $this->createRequest();
        $firstResponse = $this->processRequest($firstRequest, function (SessionInterface $session) {
            $this->setTestData($session);
        });

        // Fake login
        $secondRequest  = $this->createRequestFromResponse($firstResponse);
        $secondResponse = $this->processRequest($secondRequest, function (SessionInterface $session) {
            $this->setUserId($session);
            $session->regenerate();
        });

        // Check session regeneration
        $this->checkTokenChanged($firstResponse, $secondResponse);

        // Check data is persisted
        $thirdRequest  = $this->createRequestFromResponse($secondResponse);
        $thirdResponse = $this->processRequest($thirdRequest, function (SessionInterface $session) {
            $this->checkUserIdExists($session);
            $this->checkTestDataExists($session);
        });

        // Check token persistence
        $this->checkTokenKept($secondResponse, $thirdResponse);
    }

    public function testClearDataOnLogout(): void
    {
        // Create starter logged-in session
        // Fake User ID and random key to be cleared
        $firstRequest  = $this->createRequest();
        $firstResponse = $this->processRequest($firstRequest, function (SessionInterface $session) {
            $this->setUserId($session);
            $this->setTestData($session);
        });

        // Fake logout
        $secondReq      = $this->createRequestFromResponse($firstResponse);
        $secondResponse = $this->processRequest($secondReq, function (SessionInterface $session) {
            $this->removeUserId($session);
            $session->regenerate();
        });

        $this->checkTokenChanged($firstResponse, $secondResponse);

        // Check data removed
        $thirdReq      = $this->createRequestFromResponse($secondResponse);
        $thirdResponse = $this->processRequest($thirdReq, function (SessionInterface $session) {
            self::assertFalse($session->has(SessionHelper::AUTH_USER_ID), 'User ID has not been cleared (third)');
            self::assertFalse($session->has(self::TEST_KEY), 'Session data has not been cleared (third)');
        });

        $this->checkTokenKept($secondResponse, $thirdResponse);
    }

    public function testTransitionOnUserAgentChange(): void
    {
        // Create new session with default User-Agent
        $firstRequest  = $this->createRequest();
        $firstResponse = $this->processRequest($firstRequest, function (SessionInterface $session) {
            $this->setTestData($session);
        });

        // Make request with another User-Agent
        $secondRequest  = $this->createRequestFromResponse($firstResponse, 'Another User-Agent');
        $secondResponse = $this->processRequest($secondRequest, function (SessionInterface $session) {
            $this->checkUserIdCleared($session);
            $this->checkTestDataCleared($session);
        });

        // Check Session ID is changed
        $this->checkTokenChanged($firstResponse, $secondResponse);
    }

    private function createStorage(): SessionStorage
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
        $sessionFactory->method('create')->willReturnCallback(function (string $token): UserSessionInterface {
            $model = $this->createMock(UserSession::class);

            $model->method('getToken')->willReturn($token);
            $model->method('getCreatedAt')->willReturn(new DateTimeImmutable());

            $model->method('setContents')->willReturnCallback(function (string $value) use ($model, $token): UserSessionInterface {
                $this->sessionContent[$token] = $value;

                return $model;
            });

            $model->method('getContents')->willReturnCallback(function () use ($token): string {
                return $this->sessionContent[$token];
            });

            $this->sessionMocks[$token] = $model;

            return $model;
        });

        $sessionConfig->method('getLifetime')->willReturnCallback(function () {
            return new DateInterval('P1W');
        });

        $cookies = new CookieHelper($appConfig, $appEnv);

        return new SessionStorage($sessionFactory, $sessionRepo, $sessionConfig, $encryption, $cookies);
    }

    private function createRequest(string $userAgent = null, string $token = null): ServerRequestInterface
    {
        $req = (new ServerRequest())->withHeader('User-Agent', $userAgent ?? 'Default User-Agent');

        if ($token) {
            $req = $req->withCookieParams([
                SessionStorage::COOKIE_NAME => $token,
            ]);
        }

        return $req;
    }

    private function processRequest(ServerRequestInterface $request, callable $sessionHandler = null): ResponseInterface
    {
        return $this->middleware->process(
            $request,
            new RequestHandler(function (ServerRequestInterface $request) use ($sessionHandler) {
                $session = ServerRequestHelper::getSession($request);

                $sessionHandler && $sessionHandler($session);

                return ResponseHelper::html('OK');
            })
        );
    }

    private function createRequestFromResponse(ResponseInterface $response, string $userAgent = null): ServerRequestInterface
    {
        $token = $this->getTokenFromResponse($response);

        return $this->createRequest($userAgent, $token);
    }

    private function getTokenFromResponse(ResponseInterface $response): string
    {
        return SetCookies::fromResponse($response)->get(SessionStorage::COOKIE_NAME)->getValue();
    }

    private function checkStorageData(string $token, string $key, mixed $value): void
    {
        $this->assertArrayHasKey($token, $this->sessionContent, 'Missing session data for token '.$token);

        $data = json_decode($this->sessionContent[$token], true, 2, JSON_THROW_ON_ERROR);
        self::assertEquals($value, $data[$key], 'Session data mismatch');
    }

    private function setTestData(SessionInterface $session): void
    {
        $session->set(self::TEST_KEY, true);
    }

    private function setUserId(SessionInterface $session): void
    {
        SessionHelper::setUserID($session, self::USER_ID);
    }

    private function removeUserId(SessionInterface $session): void
    {
        SessionHelper::removeUserID($session);
    }

    private function checkTestDataExists(SessionInterface $session): void
    {
        self::assertTrue($session->has(self::TEST_KEY), 'Test data is missing');
        self::assertEquals(self::TEST_VALUE, $session->get(self::TEST_KEY), 'Test data value mismatch');
    }

    private function checkTestDataCleared(SessionInterface $session): void
    {
        self::assertFalse($session->has(self::TEST_KEY), 'Test data was kept');
    }

    private function checkUserIdExists(SessionInterface $session): void
    {
        self::assertTrue($session->has(SessionHelper::AUTH_USER_ID), 'User ID is missing');
        self::assertEquals(self::USER_ID, $session->get(SessionHelper::AUTH_USER_ID), 'User ID mismatch');
    }

    private function checkUserIdCleared(SessionInterface $session): void
    {
        self::assertFalse($session->has(SessionHelper::AUTH_USER_ID), 'User ID was kept');
    }

    private function checkTokenKept(ResponseInterface $leftRes, ResponseInterface $rightRes): void
    {
        $leftToken  = $this->getTokenFromResponse($leftRes);
        $rightToken = $this->getTokenFromResponse($rightRes);

        self::assertEquals($leftToken, $rightToken, 'Session token is not kept');
    }

    private function checkTokenChanged(ResponseInterface $leftRes, ResponseInterface $rightRes): void
    {
        $leftToken  = $this->getTokenFromResponse($leftRes);
        $rightToken = $this->getTokenFromResponse($rightRes);

        self::assertNotEquals($leftToken, $rightToken, 'Session token is not changed');
    }
}
