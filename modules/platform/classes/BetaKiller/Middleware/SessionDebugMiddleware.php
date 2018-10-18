<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\DebugBarSessionDataCollector;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionDebugMiddleware implements MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \DebugBar\DebugBarException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $debugBar = ServerRequestHelper::getDebugBar($request);

        if ($debugBar) {
            $debugBar->addCollector(new DebugBarSessionDataCollector($request));
        }

        return $handler->handle($request);
    }
}
