<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class FallbackErrorMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * FallbackErrorMiddleware constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     */
    public function __construct(AppEnvInterface $appEnv)
    {
        $this->appEnv = $appEnv;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $e) {
            if ($this->appEnv->inProductionMode()) {
                return ResponseHelper::text('', 500);
            }

            return ResponseHelper::html(\Debug::htmlStacktrace($e));
        }
    }
}
