<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\Profiler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProfilerMiddleware implements MiddlewareInterface
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
        // Fresh instance for every request
        $profiler = new Profiler();

        return $handler->handle($request->withAttribute(Profiler::class, $profiler));
    }
}
