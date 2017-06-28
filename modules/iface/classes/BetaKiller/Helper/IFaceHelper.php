<?php
namespace BetaKiller\Helper;

use BetaKiller\Exception;
use BetaKiller\IFace\CrudlsActionsInterface;
use BetaKiller\IFace\IFaceFactory;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceStack;
use BetaKiller\IFace\Url\UrlParametersInterface;
use BetaKiller\IFace\View\IFaceView;
use BetaKiller\IFace\Widget\WidgetInterface;
use BetaKiller\IFace\WidgetFactory;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\IFaceZone;
use Spotman\Api\ApiMethodResponse;

class IFaceHelper
{
    /**
     * @var \BetaKiller\IFace\IFaceFactory
     */
    private $ifaceFactory;

    /**
     * @var \BetaKiller\IFace\WidgetFactory
     */
    private $widgetFactory;

    /**
     * @var \BetaKiller\IFace\IFaceStack
     */
    private $stack;

    /**
     * @var \BetaKiller\IFace\View\IFaceView
     */
    private $view;

    /**
     * @var \BetaKiller\Helper\UrlParametersHelper
     */
    private $paramsHelper;

    /**
     * IFaceHelper constructor.
     *
     * @param \BetaKiller\IFace\View\IFaceView       $view
     * @param \BetaKiller\IFace\IFaceStack           $stack
     * @param \BetaKiller\IFace\IFaceFactory         $ifaceFactory
     * @param \BetaKiller\IFace\WidgetFactory        $widgetFactory
     * @param \BetaKiller\Helper\UrlParametersHelper $paramsHelper
     */
    public function __construct(
        IFaceView $view,
        IFaceStack $stack,
        IFaceFactory $ifaceFactory,
        WidgetFactory $widgetFactory,
        UrlParametersHelper $paramsHelper
    ) {
        $this->view          = $view;
        $this->stack         = $stack;
        $this->ifaceFactory  = $ifaceFactory;
        $this->widgetFactory = $widgetFactory;
        $this->paramsHelper  = $paramsHelper;
    }

    public function getCurrentIFace(): ?IFaceInterface
    {
        return $this->stack->getCurrent();
    }

    public function isCurrentIFaceAction(string $name): bool
    {
        $currentIFace  = $this->getCurrentIFace();
        $currentAction = $currentIFace->getEntityActionName();

        return $currentAction === $name;
    }

    public function isCurrentIFaceZone(string $zone): bool
    {
        $currentIFace = $this->getCurrentIFace();
        $currentZone  = $currentIFace->getZoneName();

        return $currentZone === $zone;
    }

    public function isCurrentIFace(IFaceInterface $iface, UrlParametersInterface $params = null): bool
    {
        return $this->stack->isCurrent($iface, $params);
    }

    public function isInStack(IFaceInterface $iface): bool
    {
        return $this->stack->has($iface);
    }

    public function renderIFace(IFaceInterface $iface): string
    {
        // Getting IFace View instance and rendering
        return $this->view->render($iface);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\IFace\IFaceInterface
     */
    public function createIFaceFromCodename(string $codename): IFaceInterface
    {
        return $this->ifaceFactory->fromCodename($codename);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return \BetaKiller\IFace\IFaceInterface
     */
    public function createIFaceFromModel(IFaceModelInterface $model): IFaceInterface
    {
        return $this->ifaceFactory->fromModel($model);
    }

    /**
     * @param $name
     *
     * @return \BetaKiller\IFace\Widget\WidgetInterface
     */
    public function createWidget(string $name): WidgetInterface
    {
        return $this->widgetFactory->create($name);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $action
     * @param string|null                                   $zone
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    public function getEntityUrl(DispatchableEntityInterface $entity, string $action, ?string $zone = null): string
    {
        if (!$zone) {
            $currentIFace = $this->getCurrentIFace();

            if (!$currentIFace) {
                throw new Exception('IFace zone must be specified');
            }

            // Fetch zone from current IFace
            $zone = $currentIFace->getZoneName();
        }

        // Search for IFace with provided entity, action and zone
        $iface = $this->ifaceFactory->getByEntityActionAndZone($entity, $action, $zone);

        // TODO Create ResolvingUrlParameters instance from current entity
        // TODO Fetch linked entities from current entity on-demand

        $params = $this->paramsHelper->createEmpty();
        $params->setEntity($entity);
        $entity->presetLinkedEntities($params);

        return $iface->url($params);
    }

    public function getCreateEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_CREATE, $zone);
    }

    public function getReadEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_READ, $zone);
    }

    public function getUpdateEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_UPDATE, $zone);
    }

    public function getDeleteEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_DELETE, $zone);
    }

    public function getListEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_LIST, $zone);
    }

    public function getSearchEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_SEARCH, $zone);
    }

    public function getPreviewEntityUrl(DispatchableEntityInterface $entity): ?string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_READ, IFaceZone::PREVIEW_ZONE);
    }

    public function processApiResponse(ApiMethodResponse $response, IFaceInterface $iface)
    {
        $iface->setLastModified($response->getLastModified());

        return $response->getData();
    }

    public function setExpiresInPast(IFaceInterface $iface): void
    {
        // No caching for admin zone
        $interval         = new \DateInterval('PT1H');
        $interval->invert = 1;

        $iface->setExpiresInterval($interval);
    }
}
