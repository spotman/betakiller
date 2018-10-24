<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Dev\Profiler;
use BetaKiller\Exception;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementStack;
use DebugBar\DebugBar;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Session\SessionInterface;
use Zend\Expressive\Session\SessionMiddleware;

class ServerRequestHelper
{
    /**
     * @var  array  trusted proxy server IPs
     */
    private static $trustedProxies = ['127.0.0.1', 'localhost', 'localhost.localdomain'];

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
    public static function getIpAddress(ServerRequestInterface $request): string
    {
        $server = $request->getServerParams();

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

    public static function getModule(ServerRequestInterface $request): ?string
    {
        // TODO How to detect module from PSR-15 request ???
        return null;
    }

    public static function getCookie(ServerRequestInterface $request, string $name): ?string
    {
        // TODO Replace with ServerRequestInterface manipulation after migration to PSR-7
        return \Cookie::get($request ? $name : null, null);
    }

    public static function isAjax(ServerRequestInterface $request): bool
    {
        $server = $request->getServerParams();

        $requestedWith = $server['HTTP_X_REQUESTED_WITH'] ?? null;

        return mb_strtolower($requestedWith) === 'xmlhttprequest';
    }

    public static function isJsonPreferred(ServerRequestInterface $request): bool
    {
        $type = self::getPreferredContentType($request);

        return \mb_strpos($type, 'json') !== false;
    }

    private static function getPreferredContentType(ServerRequestInterface $request): string
    {
        // Fetched from ContentType middleware
        return $request->getHeaderLine('Accept');
    }

    public static function getPost(ServerRequestInterface $request): array
    {
        if ($request->getMethod() !== 'POST') {
            throw new BadRequestHttpException('Post request is required');
        }

        return (array)$request->getParsedBody();
    }

    public static function getHttpReferrer(ServerRequestInterface $request): string
    {
        $server = $request->getServerParams();

        return $server['HTTP_REFERER'] ?? '';
    }

    public static function getUrlContainer(ServerRequestInterface $request): UrlContainerInterface
    {
        return $request->getAttribute(UrlContainerInterface::class);
    }

    public static function getEntity(ServerRequestInterface $request, string $className)
    {
        $container = self::getUrlContainer($request);

        return $container->getEntityByClassName($className);
    }

    public static function getParameter(ServerRequestInterface $request, string $className)
    {
        $container = self::getUrlContainer($request);

        return $container->getParameterByClassName($className);
    }

    public static function getQueryPart(ServerRequestInterface $request, string $name, bool $required = null): ?string
    {
        $container = self::getUrlContainer($request);

        return $container->getQueryPart($name, $required);
    }

    public static function getUrlElementStack(ServerRequestInterface $request): UrlElementStack
    {
        return $request->getAttribute(UrlElementStack::class);
    }

    public static function getUrlHelper(ServerRequestInterface $request): UrlHelper
    {
        return $request->getAttribute(UrlHelper::class);
    }

    public static function getUser(ServerRequestInterface $request): UserInterface
    {
        return $request->getAttribute(UserInterface::class);
    }

    public static function isGuest(ServerRequestInterface $request): bool
    {
        if (!self::hasUser($request)) {
            // No user => guest user
            return true;
        }

        $user = self::getUser($request);

        return $user->isGuest();
    }

    public static function hasUser(ServerRequestInterface $request): bool
    {
        return (bool)$request->getAttribute(UserInterface::class);
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

    public static function getI18n(ServerRequestInterface $request): I18nHelper
    {
        return $request->getAttribute(I18nHelper::class);
    }

    public static function getProfiler(ServerRequestInterface $request): Profiler
    {
        return $request->getAttribute(Profiler::class);
    }

    public static function getDebugBar(ServerRequestInterface $request): ?DebugBar
    {
        return $request->getAttribute(DebugBar::class);
    }
}
