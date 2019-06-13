<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\Profiler;
use BetaKiller\Exception\FoundHttpException;
use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Factory\UrlElementInstanceFactory;
use BetaKiller\Factory\UrlElementProcessorFactory;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Url\AfterProcessingInterface;
use BetaKiller\Url\BeforeProcessingInterface;
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
     * @var \BetaKiller\Factory\UrlElementInstanceFactory
     */
    private $instanceFactory;

    /**
     * UrlElementRenderMiddleware constructor.
     *
     * @param \BetaKiller\Factory\UrlElementProcessorFactory $processorFactory
     * @param \BetaKiller\Factory\UrlElementInstanceFactory  $instanceFactory
     */
    public function __construct(
        UrlElementProcessorFactory $processorFactory,
        UrlElementInstanceFactory $instanceFactory
    ) {
        $this->processorFactory = $processorFactory;
        $this->instanceFactory  = $instanceFactory;
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

        $stack = ServerRequestHelper::getUrlElementStack($request);

        if (!$stack->hasCurrent()) {
            throw new NotFoundHttpException;
        }

        $urlElement = $stack->getCurrent();

        $path = $request->getUri()->getPath();

        // If this is default IFace and client requested non-slash uri, redirect client to /
        if ($path !== '/' && $urlElement->isDefault() && !$urlElement->hasDynamicUrl()) {
            throw new FoundHttpException('/');
        }

        $urlProcessor = $this->processorFactory->createFromUrlElement($urlElement);
        $instance     = $this->instanceFactory->createFromUrlElement($urlElement);

        // Starting hook
        if ($instance && $instance instanceof BeforeProcessingInterface) {
            $instance->beforeProcessing($request);
        }

        $response = $urlProcessor->process($instance, $request);

        // Final hook
        if ($instance && $instance instanceof AfterProcessingInterface) {
            $instance->beforeProcessing($request);
        }

        Profiler::end($pid);

        return $response;
    }
}
