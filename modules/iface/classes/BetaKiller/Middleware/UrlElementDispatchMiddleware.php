<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Event\MissingUrlEvent;
use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Exception\SeeOtherHttpException;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Behaviour\UrlBehaviourException;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\MissingUrlElementException;
use BetaKiller\Url\UrlElementException;
use BetaKiller\Url\UrlElementStack;
use BetaKiller\Url\UrlProcessor;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class UrlElementDispatchMiddleware implements MiddlewareInterface
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Url\UrlProcessor
     */
    private $urlProcessor;

    /**
     * @var \BetaKiller\MessageBus\EventBusInterface
     */
    private $eventBus;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * UrlElementDispatchMiddleware constructor.
     *
     * @param \BetaKiller\Url\UrlProcessor             $urlProcessor
     * @param \BetaKiller\MessageBus\EventBusInterface $eventBus
     * @param \Psr\Log\LoggerInterface                 $logger
     */
    public function __construct(
        UrlProcessor $urlProcessor,
        EventBusInterface $eventBus,
        LoggerInterface $logger
    ) {
        $this->urlProcessor = $urlProcessor;
        $this->eventBus     = $eventBus;
        $this->logger       = $logger;
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
        $pid = RequestProfiler::begin($request, 'UrlElement dispatch');

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

        RequestProfiler::end($pid);

        // Forward call
        return $handler->handle($request);
    }

    private function dispatchRequest(
        ServerRequestInterface $request,
        UrlElementStack $stack,
        UrlContainerInterface $params,
        UserInterface $user
    ): void {
        $url = ServerRequestHelper::getUrl($request);

        try {
            $this->urlProcessor->process($url, $stack, $params, $user);
        } catch (MissingUrlElementException $e) {
            $parentModel = $e->getParentUrlElement();

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

            // Simply not found
            throw new NotFoundHttpException();
        } catch (UrlBehaviourException | UrlElementException $e) {
            // Log this exception and keep processing
            $this->logException($this->logger, $e);

            // Nothing found
            throw new NotFoundHttpException;
        }

        // Emit event about successful url parsing
        $this->eventBus->emit(new UrlDispatchedEvent($request));
    }
}
