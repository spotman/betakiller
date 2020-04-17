<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class UserDebugMiddleware implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (DebugServerRequestHelper::hasDebugBar($request) && ServerRequestHelper::hasUser($request)) {
            $debugBar = DebugServerRequestHelper::getDebugBar($request);

            // Fetch actual user
            $user = ServerRequestHelper::getUser($request);

            // Add debug info
            $debugBar->addCollector(new DebugBarUserDataCollector($user));
        }

        return $handler->handle($request);
    }
}
