<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\Profiler;
use BetaKiller\Factory\UrlElementProcessorFactory;
use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UrlElementRenderMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Factory\UrlElementProcessorFactory
     */
    private $processorFactory;

    /**
     * UrlElementRenderMiddleware constructor.
     *
     * @param \BetaKiller\Factory\UrlElementProcessorFactory $processorFactory
     */
    public function __construct(UrlElementProcessorFactory $processorFactory)
    {
        $this->processorFactory = $processorFactory;
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
        $pid = Profiler::begin($request, 'UrlElement processing');

        $stack    = ServerRequestHelper::getUrlElementStack($request);

        $urlElement   = $stack->getCurrent();
        $urlProcessor = $this->processorFactory->createFromUrlElement($urlElement);

        $response = $urlProcessor->process($urlElement, $request);

        Profiler::end($pid);

        return $response;
    }
}
