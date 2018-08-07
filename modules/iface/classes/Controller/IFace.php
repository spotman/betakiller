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
    private $factoryProcessor;

    /**
     * @Inject
     * @var UrlDispatcher
     */
    private $urlDispatcher;

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
        $urlElement   = $this->urlDispatcher->process(
            $this->request->url(),
            $this->request->client_ip(),
            $this->request->referrer()
        );
        $urlProcessor = $this->factoryProcessor->createFromUrlElement($urlElement);
        $urlProcessor
            ->setRequest($this->request)
            ->setResponse($this->response)
            ->process();
    }
}
