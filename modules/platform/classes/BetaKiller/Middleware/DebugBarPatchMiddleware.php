<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\DebugBarHttpDriver;
use BetaKiller\Dev\DebugBarSessionDataCollector;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DebugBarPatchMiddleware implements MiddlewareInterface
{
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
        $debugBar = ServerRequestHelper::getDebugBar($request);

        if (!$debugBar) {
            // No debug bar => simple forward call
            return $handler->handle($request);
        }

        // Fetch actual session
        $session = ServerRequestHelper::getSession($request);

        // Initialize http driver
        $httpDriver = new DebugBarHttpDriver($session);
        $debugBar->setHttpDriver($httpDriver);

        // Add session tab with session data
        $debugBar->addCollector(new DebugBarSessionDataCollector($session));

        // Forward call
        $response = $handler->handle($request);

        // Add headers injected by DebugBar
        return $httpDriver->applyHeaders($response);
    }
}
