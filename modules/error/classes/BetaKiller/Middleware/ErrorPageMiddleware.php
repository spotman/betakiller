<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Error\ErrorPageRendererInterface;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class ErrorPageMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Error\ErrorPageRendererInterface
     */
    private $renderer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ErrorPageMiddleware constructor.
     *
     * @param \BetaKiller\Error\ErrorPageRendererInterface $renderer
     * @param \Psr\Log\LoggerInterface                     $logger
     */
    public function __construct(ErrorPageRendererInterface $renderer, LoggerInterface $logger)
    {
        $this->renderer = $renderer;
        $this->logger   = $logger;
    }

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
        try {
            return $handler->handle($request);
        } catch (\Throwable $e) {
            // Logging exception
            LoggerHelper::logRequestException($this->logger, $e, $request);

            $response = $this->renderer->render($request, $e);

            return ResponseHelper::disableCaching($response);
        }
    }
}
