<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Env\AppEnvInterface;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final class FallbackErrorMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Env\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * FallbackErrorMiddleware constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface $appEnv
     * @param \Psr\Log\LoggerInterface        $logger
     */
    public function __construct(AppEnvInterface $appEnv, LoggerInterface $logger)
    {
        $this->appEnv = $appEnv;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $e) {
            // Keep minimal data (no session at this point)
            LoggerHelper::logRawException($this->logger, $e);

            return $this->appEnv->inProductionMode()
                ? ResponseHelper::text('System error', 500)
                : \Debug::renderStackTrace($e, $request);
        }
    }
}
