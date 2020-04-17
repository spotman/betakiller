<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Helper\AppEnvInterface;
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
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * FallbackErrorMiddleware constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     * @param \Psr\Log\LoggerInterface           $logger
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

            if ($this->appEnv->inProductionMode()) {
                return ResponseHelper::text('', 500);
            }

            \Debug::injectStackTraceCsp($request);

            return ResponseHelper::html(\Debug::htmlStacktrace($e), 500);
        }
    }
}
