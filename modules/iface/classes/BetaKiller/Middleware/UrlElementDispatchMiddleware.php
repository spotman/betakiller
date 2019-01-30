<?php
declare(strict_types=1);

namespace BetaKiller\Middleware;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Dev\Profiler;
use BetaKiller\Event\MissingUrlEvent;
use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Exception\SeeOtherHttpException;
use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Behaviour\UrlBehaviourException;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\MissingUrlElementException;
use BetaKiller\Url\UrlDispatcherInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementStack;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class UrlElementDispatchMiddleware implements MiddlewareInterface
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Url\UrlDispatcherInterface
     */
    private $urlDispatcher;

    /**
     * @var \BetaKiller\Factory\UrlElementProcessorFactory
     */
    private $processorFactory;

    /**
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

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
     * @param \BetaKiller\Url\UrlDispatcherInterface   $urlDispatcher
     * @param \BetaKiller\Helper\AclHelper             $aclHelper
     * @param \BetaKiller\MessageBus\EventBusInterface $eventBus
     * @param \Psr\Log\LoggerInterface                 $logger
     */
    public function __construct(
        UrlDispatcherInterface $urlDispatcher,
        EventBusInterface $eventBus,
        AclHelper $aclHelper,
        LoggerInterface $logger
    ) {
        $this->urlDispatcher = $urlDispatcher;
        $this->aclHelper     = $aclHelper;
        $this->eventBus      = $eventBus;
        $this->logger        = $logger;
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
        $pid = Profiler::begin($request, 'UrlElement dispatch');

        $stack  = ServerRequestHelper::getUrlElementStack($request);
        $params = ServerRequestHelper::getUrlContainer($request);
        $user   = ServerRequestHelper::getUser($request);

        $this->dispatchRequest($request, $stack, $params);

        // Check current user access for all URL elements
        foreach ($stack as $urlElement) {
            $this->checkUrlElementAccess($urlElement, $params, $user);
        }

        Profiler::end($pid);

        // Forward call
        return $handler->handle($request);
    }

    private function dispatchRequest(
        ServerRequestInterface $request,
        UrlElementStack $stack,
        UrlContainerInterface $params
    ): void {
        $url = ServerRequestHelper::getUrl($request);

        try {
            $this->urlDispatcher->process($url, $stack, $params);
        } catch (MissingUrlElementException $e) {
            $parentModel = $e->getParentUrlElement();

            $urlHelper = ServerRequestHelper::getUrlHelper($request);

            $redirectToUrl = $parentModel && $e->getRedirectToParent()
                ? $urlHelper->makeUrl($parentModel, $params, false)
                : null;

            $this->eventBus->emit(new MissingUrlEvent($request, $parentModel, $redirectToUrl));

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

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParameters
     * @param \BetaKiller\Model\UserInterface                 $user
     *
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \Spotman\Acl\Exception
     */
    private function checkUrlElementAccess(
        UrlElementInterface $urlElement,
        UrlContainerInterface $urlParameters,
        UserInterface $user
    ): void {
        // Force authorization for non-public zones before security check
        $this->aclHelper->forceAuthorizationIfNeeded($urlElement, $user);

        if (!$this->aclHelper->isUrlElementAllowed($user, $urlElement, $urlParameters)) {
            throw new AccessDeniedException();
        }
    }
}
