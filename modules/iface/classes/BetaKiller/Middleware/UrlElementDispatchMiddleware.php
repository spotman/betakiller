<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Event\MissingUrlEvent;
use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\Exception\SeeOtherHttpException;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\MissingUrlElementException;
use BetaKiller\Url\UrlElementStack;
use BetaKiller\Url\UrlProcessor;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UrlElementDispatchMiddleware implements MiddlewareInterface
{
    /**
     * @var \BetaKiller\Url\UrlProcessor
     */
    private $urlProcessor;

    /**
     * @var \BetaKiller\MessageBus\EventBusInterface
     */
    private $eventBus;

    /**
     * UrlElementDispatchMiddleware constructor.
     *
     * @param \BetaKiller\Url\UrlProcessor             $urlProcessor
     * @param \BetaKiller\MessageBus\EventBusInterface $eventBus
     */
    public function __construct(
        UrlProcessor $urlProcessor,
        EventBusInterface $eventBus
    ) {
        $this->urlProcessor = $urlProcessor;
        $this->eventBus     = $eventBus;
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
        $stack  = ServerRequestHelper::getUrlElementStack($request);
        $params = ServerRequestHelper::getUrlContainer($request);
        $user   = ServerRequestHelper::getUser($request);
        $i18n   = ServerRequestHelper::getI18n($request);

        $this->dispatchRequest($request, $stack, $params, $user);

        /** @var LanguageInterface $lang */
        $lang = ServerRequestHelper::getEntity($request, LanguageInterface::class);

        // Override i18n language with parsed model (if exists)
        if ($lang) {
            $i18n->setLang($lang);
        }

        // Forward call
        return $handler->handle($request);
    }

    private function dispatchRequest(
        ServerRequestInterface $request,
        UrlElementStack $stack,
        UrlContainerInterface $params,
        UserInterface $user
    ): void {
        $pid = RequestProfiler::begin($request, 'UrlElement dispatch');

        $url = ServerRequestHelper::getUrl($request);

        try {
            $this->urlProcessor->process($url, $stack, $params, $user);

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
