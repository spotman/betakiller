<?php

declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\ResponseHelper;
use Debug;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class FallbackErrorMiddleware implements MiddlewareInterface
{
    public function __construct(private AppEnvInterface $appEnv, private LoggerInterface $logger)
    {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            // Keep minimal data (no session at this point)
            LoggerHelper::logRawException($this->logger, $e);

            $response = $this->appEnv->inProductionMode()
                ? ResponseHelper::text('System error', 500)
                : Debug::renderStackTrace($e, $request);

            return $response->withHeader('X-Error-Handler', 'fallback-middleware');
        }
    }
}
