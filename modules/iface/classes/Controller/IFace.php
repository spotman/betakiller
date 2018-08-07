<?php
use BetaKiller\Url\UrlDispatcher;

/**
 * Class Controller_IFace
 */
class Controller_IFace extends Controller
{
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
     * @deprecated
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Exception\SeeOtherHttpException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\MessageBus\MessageBusException
     * @throws \Psr\SimpleCache\InvalidArgumentException
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
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Spotman\Acl\Exception
     */
    public function actionRender(): void
    {
        $this->urlContainer->setQueryParts($this->request->query());

        $urlElement   = $this->urlDispatcher->process(
            $this->request->url(),
            $this->request->client_ip(),
            $this->request->referrer()
        );
        $urlProcessor = $this->processorFactory->createFromUrlElement($urlElement);
        $urlProcessor->process($urlElement, $this->urlContainer, $this->response, $this->request);
    }
}
