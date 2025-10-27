<?php

declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Error\ErrorPageRendererInterface;
use BetaKiller\Exception\HttpExceptionInterface;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class ErrorPageMiddleware implements MiddlewareInterface
{
    public function __construct(private ErrorPageRendererInterface $renderer, private LoggerInterface $logger)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            $isIgnored = $e instanceof HttpExceptionInterface && !$e->isServerError();

            if (!$isIgnored) {
                // Log non-HTTP exception
                LoggerHelper::logRequestException($this->logger, $e, $request);
            }

            $response = $this->renderer->render($request, $e);

            return ResponseHelper::disableCaching($response);
        }
    }
}
