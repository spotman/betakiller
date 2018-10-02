<?php

use BetaKiller\Event\MissingUrlEvent;
use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Exception\SeeOtherHttpException;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\Url\Behaviour\UrlBehaviourException;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\MissingUrlElementException;
use BetaKiller\Url\UrlDispatcher;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementStack;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Controller_UrlElement
 */
class Controller_UrlElement extends Controller
{
    use LoggerHelperTrait;

    /**
     * @Inject
     * @var \BetaKiller\Factory\UrlElementProcessorFactory
     */
    private $processorFactory;

    /**
     * @Inject
     * @var UrlDispatcher
     */
    private $urlDispatcher;

    /**
     * Manager of URL element parameters
     *
     * @Inject
     * @var \BetaKiller\Url\Container\UrlContainerInterface
     */
    private $urlContainer;

    /**
     * @Inject
     * @var \BetaKiller\Url\UrlElementStack
     */
    private $urlElementStack;

    /**
     * @Inject
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

    /**
     * @Inject
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * @Inject
     * @var \BetaKiller\MessageBus\EventBusInterface
     */
    private $eventBus;

    /**
     * @Inject
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $psrRequest;

    /**
     * @Inject
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @Inject
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @deprecated
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Exception\SeeOtherHttpException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\MessageBus\MessageBusException
     * @throws \Spotman\Acl\Exception
     */
    public function action_render(): void
    {
        $this->actionRender();
    }

    /**
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Exception\SeeOtherHttpException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\MessageBus\MessageBusException
     * @throws \Spotman\Acl\Exception
     */
    public function actionRender(): void
    {
        $this->dispatchRequest($this->psrRequest, $this->urlElementStack, $this->urlContainer);

        // Check current user access for all URL elements
        foreach ($this->urlElementStack as $urlElement) {
            $this->checkUrlElementAccess($urlElement, $this->urlContainer);
        }

        $urlElement = $this->urlElementStack->getCurrent();

        $urlProcessor = $this->processorFactory->createFromUrlElement($urlElement);
        $urlProcessor->process($urlElement, $this->urlContainer, $this->psrRequest, $this->response);
    }

    private function dispatchRequest(
        ServerRequestInterface $request,
        UrlElementStack $stack,
        UrlContainerInterface $params
    ): void {
        $this->urlContainer->setQueryParts($request->getQueryParams());

        $url       = $this->request->url();
        $ipAddress = $this->request->getClientIp();
        $referrer  = $this->request->referrer();

        try {
            $this->urlDispatcher->process($url, $stack, $params);
        } catch (MissingUrlElementException $e) {
            $parentModel = $e->getParentUrlElement();

            $redirectToUrl = $parentModel && $e->getRedirectToParent()
                ? $this->urlHelper->makeUrl($parentModel, $params, false)
                : null;

            $this->eventBus->emit(new MissingUrlEvent($url, $parentModel, $ipAddress, $referrer, $redirectToUrl));

            if ($redirectToUrl) {
                // Missing but see other
                throw new SeeOtherHttpException($redirectToUrl);
            }

            // Simply not found
            throw new NotFoundHttpException();
        } catch (UrlBehaviourException | IFaceException $e) {
            // Log this exception and keep processing
            $this->logException($this->logger, $e);

            // Nothing found
            throw new NotFoundHttpException;
        }

        // Emit event about successful url parsing
        $this->eventBus->emit(new UrlDispatchedEvent($url, $params, $ipAddress, $referrer));
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $urlElement
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParameters
     *
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    private function checkUrlElementAccess(UrlElementInterface $urlElement, UrlContainerInterface $urlParameters): void
    {
        // Force authorization for non-public zones before security check
        $this->aclHelper->forceAuthorizationIfNeeded($urlElement, $this->user);

        if (!$this->aclHelper->isUrlElementAllowed($this->user, $urlElement, $urlParameters)) {
            throw new \BetaKiller\Auth\AccessDeniedException();
        }
    }
}
