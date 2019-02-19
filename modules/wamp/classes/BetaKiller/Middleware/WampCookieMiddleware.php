<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Session\DatabaseSessionStorage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WampCookieMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $csp = ServerRequestHelper::getCsp($request);

        // This cookie is used by WAMP JS client as auth id
        $csp->protectedCookie(DatabaseSessionStorage::COOKIE_NAME, $csp::COOKIE_REMOVE | $csp::COOKIE_NAME);

        return $handler->handle($request);
    }
}
