<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Event\MissingUrlEvent;
use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\Exception\SeeOtherHttpException;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\Url\MissingUrlElementException;
use BetaKiller\Url\RequestDispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UrlElementDispatchMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Url\RequestDispatcher
     */
    private RequestDispatcher $requestDispatcher;

    /**
     * @var \BetaKiller\MessageBus\EventBusInterface
     */
    private EventBusInterface $eventBus;

    /**
     * UrlElementDispatchMiddleware constructor.
     *
     * @param \BetaKiller\Url\RequestDispatcher        $urlProcessor
     * @param \BetaKiller\MessageBus\EventBusInterface $eventBus
     */
    public function __construct(
        RequestDispatcher $urlProcessor,
        EventBusInterface $eventBus
    ) {
        $this->requestDispatcher = $urlProcessor;
        $this->eventBus          = $eventBus;
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
        $this->dispatchRequest($request);

        // Forward call
        return $handler->handle($request);
    }

    private function dispatchRequest(ServerRequestInterface $request): void
    {
        $pid = RequestProfiler::begin($request, 'UrlElement dispatch');

        try {
            $this->requestDispatcher->process($request);

            // Emit event about successful url parsing
            $this->eventBus->emit(new UrlDispatchedEvent($request));
        } catch (MissingUrlElementException $e) {
            // Do not log missing pages, they will be fetched by hit-stat module
//            LoggerHelper::logRequestException($this->logger, $e, $request);

            $this->processMissingUrl($request, $e);
        } finally {
            RequestProfiler::end($pid);
        }
    }

    private function processMissingUrl(ServerRequestInterface $request, MissingUrlElementException $e): void
    {
        $parentModel = $e->getParentUrlElement();
        $params      = $e->getUrlContainer();

        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $ip        = ServerRequestHelper::getIpAddress($request);
        $referrer  = ServerRequestHelper::getHttpReferrer($request);

        $redirectToUrl = $parentModel && $e->getRedirectToParent()
            ? $urlHelper->makeUrl($parentModel, $params, false)
            : null;

        $this->eventBus->emit(
            new MissingUrlEvent($request->getUri(), $parentModel, $redirectToUrl, $ip, $referrer)
        );

        if ($redirectToUrl) {
            // Missing but see other
            throw new SeeOtherHttpException($redirectToUrl);
        }

        // Allow custom error page processing
        throw $e;
    }
}
