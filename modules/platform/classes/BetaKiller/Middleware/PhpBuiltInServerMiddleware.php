<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\TextHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class PhpBuiltInServerMiddleware implements MiddlewareInterface
{
    public const HTTP_HEADER_NAME  = 'X-Php-Built-In-Server';
    public const HTTP_HEADER_VALUE = 'yes';

    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private AppEnvInterface $appEnv;

    /**
     * PhpBuiltInServerMiddleware constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv)
    {
        $this->appEnv = $appEnv;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (!$this->appEnv->isInternalWebServer()) {
            return $response;
        }

        // Built-in web-server does not support HTTPS
        if ($response->hasHeader('Location')) {
            $redirectUrl = $response->getHeaderLine('Location');

            $redirectUrl = TextHelper::replaceFirst($redirectUrl, 'https://', 'http://');

            $response = $response->withHeader('Location', $redirectUrl);
        }

        return $response->withHeader(self::HTTP_HEADER_NAME, self::HTTP_HEADER_VALUE);
    }
}
