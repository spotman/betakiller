<?php
namespace BetaKiller\Helper;

use BetaKiller\IFace\CrudlsActionsInterface;
use BetaKiller\IFace\IFaceFactory;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceStack;
use BetaKiller\IFace\Url\UrlParametersInterface;
use BetaKiller\IFace\View\IFaceView;
use BetaKiller\IFace\WidgetFactory;
use BetaKiller\Model\DispatchableEntityInterface;

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
    )
    {
        $this->view          = $view;
        $this->stack         = $stack;
        $this->ifaceFactory  = $ifaceFactory;
        $this->widgetFactory = $widgetFactory;
        $this->paramsHelper  = $paramsHelper;
    }

    public function getCurrentIFace()
    {
        return $this->stack->getCurrent();
    }

    public function isCurrentIFace(IFaceInterface $iface, UrlParametersInterface $params = null)
    {
        return $this->stack->isCurrent($iface, $params);
    }

    public function isInStack(IFaceInterface $iface)
    {
        return $this->stack->has($iface);
    }

    public function renderIFace(IFaceInterface $iface)
    {
        // Getting IFace View instance and rendering
        return $this->view->render($iface);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\IFace\IFaceInterface
     */
    public function createIFaceFromCodename($codename)
    {
        return $this->ifaceFactory->fromCodename($codename);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return \BetaKiller\IFace\IFaceInterface
     */
    public function createIFaceFromModel(IFaceModelInterface $model)
    {
        return $this->ifaceFactory->fromModel($model);
    }

    /**
     * @param $name
     *
     * @return \BetaKiller\IFace\Widget\WidgetInterface
     */
    public function createWidget($name)
    {
        return $this->widgetFactory->create($name);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $action
     * @param string|null                                   $zone
     *
     * @return string
     */
    public function getEntityUrl(DispatchableEntityInterface $entity, $action, $zone = null)
    {
        if (!$zone) {
            // Fetch zone from current IFace
            $zone = $this->getCurrentIFace()->getZoneName();
        }

        // Search for IFace with provided entity, action and zone
        $iface = $this->ifaceFactory->getByEntityActionAndZone($entity, $action, $zone);

        // TODO Create ResolvingUrlParameters instance from current entity
        // TODO Fetch linked entities from current entity on-demand

        $params = $this->paramsHelper->createEmpty();
        $params->setEntity($entity);
        $entity->presetLinkedModels($params);

        return $iface->url($params);
    }

    public function getCreateEntityUrl(DispatchableEntityInterface $entity, $zone = null)
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_CREATE, $zone);
    }

    public function getReadEntityUrl(DispatchableEntityInterface $entity, $zone = null)
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_READ, $zone);
    }

    public function getUpdateEntityUrl(DispatchableEntityInterface $entity, $zone = null)
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_UPDATE, $zone);
    }

    public function getDeleteEntityUrl(DispatchableEntityInterface $entity, $zone = null)
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_DELETE, $zone);
    }

    public function getListEntityUrl(DispatchableEntityInterface $entity, $zone = null)
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_LIST, $zone);
    }

    public function getSearchEntityUrl(DispatchableEntityInterface $entity, $zone = null)
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_SEARCH, $zone);
    }
}
