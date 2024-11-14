<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use Aidantwoods\SecureHeaders\SecureHeaders;
use BetaKiller\Auth\RequestUserProvider;
use BetaKiller\Exception;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementStack;
use PhpMiddleware\RequestId\RequestIdMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Router\RouteResult;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;

class ServerRequestHelper
{
    /**
     * @var  array  trusted proxy server IPs
     */
    private static array $trustedProxies = ['127.0.0.1', 'localhost', 'localhost.localdomain'];

    public static function addTrustedProxy(string $proxy): void
    {
        self::$trustedProxies[] = $proxy;
    }

    public static function getUserAgent(ServerRequestInterface $request): ?string
    {
        $serverParams = $request->getServerParams();

        return $serverParams['HTTP_USER_AGENT'] ?? null;
    }

    /**
     * TODO Replace with middleware migration to PSR-7
     *
     * @see https://github.com/akrabat/ip-address-middleware
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    public static function getIpAddress(ServerRequestInterface $request = null): string
    {
        $server = $request ? $request->getServerParams() : $_SERVER;

        $xForwardedFor = $server['HTTP_X_FORWARDED_FOR'] ?? null;
        $remoteAddress = $server['REMOTE_ADDR'] ?? null;
        $httpClientIP  = $server['HTTP_CLIENT_IP'] ?? null;

        if ($xForwardedFor && $remoteAddress && \in_array($remoteAddress, self::$trustedProxies, true)) {
            // Use the forwarded IP address, typically set when the
            // client is using a proxy server.
            // Format: "X-Forwarded-For: client1, proxy1, proxy2"
            $clientIPs = explode(',', $xForwardedFor);

            return array_shift($clientIPs);
        }

        if ($httpClientIP && $remoteAddress && \in_array($remoteAddress, self::$trustedProxies, true)) {
            // Use the forwarded IP address, typically set when the
            // client is using a proxy server.
            $clientIPs = explode(',', $httpClientIP);

            return array_shift($clientIPs);
        }

        if (!$remoteAddress) {
            throw new Exception('Can not determine IP address');
        }

        // The remote IP address
        return (string)$remoteAddress;
    }

    public static function getUrl(ServerRequestInterface $request): string
    {
        return $request->getRequestTarget();
    }

    /**
     * Detect module from PSR-15 request
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|string
     */
    public static function getModule(ServerRequestInterface $request): ?string
    {
        $routeResult = self::getRouteResult($request);

        if (!$routeResult) {
            return null;
        }

        $routeName = $routeResult->getMatchedRouteName();

        if (!$routeName) {
            return null;
        }

        $nameArr = explode('-', $routeName);

        return \array_shift($nameArr);
    }

    public static function getRouteResult(ServerRequestInterface $request): ?RouteResult
    {
        return $request->getAttribute(RouteResult::class);
    }

    public static function isAjax(ServerRequestInterface $request): bool
    {
        $server = $request->getServerParams();

        $requestedWith = $server['HTTP_X_REQUESTED_WITH'] ?? '';

        return mb_strtolower($requestedWith) === 'xmlhttprequest';
    }

    public static function isSecure(ServerRequestInterface $request): bool
    {
        return $request->getUri()->getScheme() === 'https';
    }

    public static function isJsonPreferred(ServerRequestInterface $request): bool
    {
        $type = self::getPreferredContentType($request);

        return \mb_strpos($type, 'json') !== false;
    }

    public static function isHtmlPreferred(ServerRequestInterface $request): bool
    {
        $type = self::getPreferredContentType($request);

        return \mb_strpos($type, 'html') !== false;
    }

    public static function isImagePreferred(ServerRequestInterface $request): bool
    {
        $type = self::getPreferredContentType($request);

        return \mb_strpos($type, 'image') !== false;
    }

    private static function getPreferredContentType(ServerRequestInterface $request): string
    {
        // Fetched from ContentType middleware
        return $request->getHeaderLine('Accept');
    }

    public static function getContentType(ServerRequestInterface $request): string
    {
        return $request->getHeaderLine('Content-Type');
    }

    public static function getPost(ServerRequestInterface $request): array
    {
        if ($request->getMethod() !== 'POST') {
            throw new BadRequestHttpException('Post request is required');
        }

        return (array)$request->getParsedBody();
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string|null
     */
    public static function getHttpReferrer(ServerRequestInterface $request): ?string
    {
        $server = $request->getServerParams();

        return $server['HTTP_REFERER'] ?? null;
    }

    public static function getUrlContainer(ServerRequestInterface $request): UrlContainerInterface
    {
        return $request->getAttribute(UrlContainerInterface::class);
    }

    public static function getEntity(ServerRequestInterface $request, string $className)
    {
        return self::getUrlContainer($request)->getEntityByClassName($className);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param string                                   $className
     *
     * @return \BetaKiller\Url\Parameter\RawUrlParameterInterface|mixed|null
     * @throws \BetaKiller\Url\Container\UrlContainerException
     */
    public static function getParameter(ServerRequestInterface $request, string $className)
    {
        return self::getUrlContainer($request)->getParameterByClassName($className);
    }

    public static function getQueryPart(ServerRequestInterface $request, string $name, bool $required = null): ?string
    {
        return self::getUrlContainer($request)->getQueryPart($name, $required);
    }

    public static function getUrlElementStack(ServerRequestInterface $request): UrlElementStack
    {
        return $request->getAttribute(UrlElementStack::class);
    }

    public static function hasUrlHelper(ServerRequestInterface $request): bool
    {
        return (bool)$request->getAttribute(UrlHelperInterface::class);
    }

    public static function getUrlHelper(ServerRequestInterface $request): UrlHelperInterface
    {
        return $request->getAttribute(UrlHelperInterface::class);
    }

    public static function setUserProvider(
        ServerRequestInterface $request,
        RequestUserProvider $provider
    ): ServerRequestInterface {
        return $request->withAttribute(RequestUserProvider::class, $provider);
    }

    public static function getUser(ServerRequestInterface $request): UserInterface
    {
        return self::getUserProvider($request)->fetch();
    }

    private static function getUserProvider(ServerRequestInterface $request): RequestUserProvider
    {
        /** @var RequestUserProvider $provider */
        $provider = $request->getAttribute(RequestUserProvider::class);

        if (!$provider) {
            throw new \LogicException('RequestUserProvider is missing');
        }

        return $provider;
    }

    public static function isGuest(ServerRequestInterface $request): bool
    {
        $session = self::getSession($request);

        return !SessionHelper::hasUserID($session);
    }

    public static function hasUserProvider(ServerRequestInterface $request): bool
    {
        // Check RequestUserProvider is exists at this time
        return (bool)$request->getAttribute(RequestUserProvider::class);
    }

    public static function isUserFetched(ServerRequestInterface $request): bool
    {
        return self::getUserProvider($request)->isFetched();
    }

    public static function getSession(ServerRequestInterface $request): SessionInterface
    {
        return $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
    }

    public static function getPreferredLanguage(ServerRequestInterface $request): string
    {
        // Fetched from ContentLanguage middleware
        return $request->getHeaderLine('Accept-Language');
    }

    public static function getI18n(ServerRequestInterface $request): RequestLanguageHelperInterface
    {
        return $request->getAttribute(RequestLanguageHelperInterface::class);
    }

    public static function hasI18n(ServerRequestInterface $request): bool
    {
        return (bool)$request->getAttribute(RequestLanguageHelperInterface::class);
    }

    public static function getCsp(ServerRequestInterface $request): ?SecureHeaders
    {
        return $request->getAttribute(SecureHeaders::class);
    }

    public static function getFlash(ServerRequestInterface $request): FlashMessagesInterface
    {
        return $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
    }

    public static function removeQueryParams(ServerRequestInterface $request, array $params): ServerRequestInterface
    {
        $targetUri   = $request->getUri();
        $targetQuery = $request->getQueryParams();

        foreach ($params as $key) {
            unset($targetQuery[$key]);
        }

        $targetUri = $targetUri->withQuery(\http_build_query($targetQuery));

        /** @var ServerRequestInterface $request */
        $request = $request->withQueryParams($targetQuery);

        return $request->withUri($targetUri);
    }

    public static function getRequestUuid(ServerRequestInterface $request): ?UuidInterface
    {
        $value = $request->getAttribute(RequestIdMiddleware::ATTRIBUTE_NAME);

        return $value ? Uuid::fromString($value) : null;
    }
}
