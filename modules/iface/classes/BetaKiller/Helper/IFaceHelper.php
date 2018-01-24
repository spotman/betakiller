<?php
namespace BetaKiller\Helper;

use BetaKiller\IFace\CrudlsActionsInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceModelTree;
use BetaKiller\IFace\IFaceProvider;
use BetaKiller\IFace\IFaceStack;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\IFaceZone;
use BetaKiller\Url\UrlContainerInterface;
use Spotman\Api\ApiMethodResponse;

class IFaceHelper
{
    /**
     * @var \BetaKiller\IFace\IFaceStack
     */
    private $stack;

    /**
     * @var \BetaKiller\Helper\UrlContainerHelper
     */
    private $paramsHelper;

    /**
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * @var \BetaKiller\IFace\IFaceProvider
     */
    private $provider;

    /**
     * @var \BetaKiller\IFace\IFaceModelTree
     */
    private $tree;

    /**
     * @var \BetaKiller\Helper\StringPatternHelper
     */
    private $stringPatternHelper;

    /**
     * IFaceHelper constructor.
     *
     * @param \BetaKiller\IFace\IFaceStack           $stack
     * @param \BetaKiller\Helper\UrlContainerHelper  $paramsHelper
     * @param \BetaKiller\IFace\IFaceProvider        $provider
     * @param \BetaKiller\IFace\IFaceModelTree       $tree
     * @param \BetaKiller\Helper\UrlHelper           $urlHelper
     * @param \BetaKiller\Helper\StringPatternHelper $stringPatternHelper
     */
    public function __construct(
        IFaceStack $stack,
        UrlContainerHelper $paramsHelper,
        IFaceProvider $provider,
        IFaceModelTree $tree,
        UrlHelper $urlHelper,
        StringPatternHelper $stringPatternHelper
    ) {
        $this->stack               = $stack;
        $this->paramsHelper        = $paramsHelper;
        $this->provider            = $provider;
        $this->urlHelper           = $urlHelper;
        $this->tree                = $tree;
        $this->stringPatternHelper = $stringPatternHelper;
    }

    public function getCurrentIFace(): ?IFaceInterface
    {
        return $this->stack->getCurrent();
    }

    public function getCurrentIFaceModel(): ?IFaceModelInterface
    {
        $current = $this->getCurrentIFace();

        return $current ? $current->getModel() : null;
    }

    public function isCurrentIFaceAction(string $name): bool
    {
        $currentIFaceModel = $this->getCurrentIFaceModel();
        $currentAction     = $currentIFaceModel ? $currentIFaceModel->getEntityActionName() : null;

        return $currentAction === $name;
    }

    public function isCurrentIFaceZone(string $zone): bool
    {
        $currentIFaceModel = $this->getCurrentIFaceModel();
        $currentZone       = $currentIFaceModel ? $this->detectZoneName($currentIFaceModel) : null;

        return $currentZone === $zone;
    }

    public function isCurrentIFace(IFaceInterface $iface, UrlContainerInterface $params = null): bool
    {
        return $this->stack->isCurrent($iface, $params);
    }

    public function isInStack(IFaceInterface $iface): bool
    {
        return $this->stack->has($iface);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function createIFaceFromCodename(string $codename): IFaceInterface
    {
        return $this->provider->fromCodename($codename);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $action
     * @param string|null                                   $zone
     *
     * @param bool|null                                     $removeCycling
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getEntityUrl(
        DispatchableEntityInterface $entity,
        string $action,
        ?string $zone = null,
        ?bool $removeCycling = null
    ): string {
        if (!$zone) {
            $currentIFaceModel = $this->getCurrentIFaceModel();

            if (!$currentIFaceModel) {
                throw new IFaceException('IFace zone must be specified');
            }

            // Fetch zone from current IFace
            $zone = $this->detectZoneName($currentIFaceModel);
        }

        $params = $this->paramsHelper->createEmpty();
        $params->setParameter($entity);

        // Search for IFace with provided entity, action and zone
        $iface = $this->provider->getByEntityActionAndZone($entity, $action, $zone);

        return $this->makeUrl($iface->getModel(), $params, $removeCycling);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param null|string                                   $zone
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getCreateEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_CREATE, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param null|string                                   $zone
     *
     * @param bool|null                                     $removeCycling
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getReadEntityUrl(
        DispatchableEntityInterface $entity,
        ?string $zone = null,
        ?bool $removeCycling = null
    ): string {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_READ, $zone, $removeCycling);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param null|string                                   $zone
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getUpdateEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_UPDATE, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param null|string                                   $zone
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getDeleteEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_DELETE, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param null|string                                   $zone
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getListEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_LIST, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param null|string                                   $zone
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getSearchEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_SEARCH, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     *
     * @return null|string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getPreviewEntityUrl(DispatchableEntityInterface $entity): ?string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_READ, IFaceZone::PREVIEW_ZONE);
    }

    /**
     * @param \Spotman\Api\ApiMethodResponse   $response
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @return mixed
     */
    public function processApiResponse(ApiMethodResponse $response, IFaceInterface $iface)
    {
        $iface->setLastModified($response->getLastModified());

        return $response->getData();
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @throws \Exception
     */
    public function setExpiresInPast(IFaceInterface $iface): void
    {
        // No caching for admin zone
        $interval         = new \DateInterval('PT1H');
        $interval->invert = 1;

        $iface->setExpiresInterval($interval);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface      $ifaceModel
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     * @param bool|null                                  $removeCyclingLinks
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function makeUrl(
        IFaceModelInterface $ifaceModel,
        ?UrlContainerInterface $params = null,
        ?bool $removeCyclingLinks = null
    ): string {
        return $this->urlHelper->makeIFaceUrl($ifaceModel, $params, $removeCyclingLinks);
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface           $iface
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     * @param bool|null                                  $removeCyclingLinks
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function makeIFaceUrl(
        IFaceInterface $iface,
        ?UrlContainerInterface $params = null,
        ?bool $removeCyclingLinks = null
    ): string {
        return $this->makeUrl($iface->getModel(), $params, $removeCyclingLinks);
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     * @deprecated Do not use direct parent search, use special iterators for traversing up and down the IFaceTree
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getIFaceParent(IFaceInterface $iface): ?IFaceInterface
    {
        return $this->provider->getParent($iface);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|null
     */
    public function detectPrimaryEntity(IFaceModelInterface $model): ?DispatchableEntityInterface
    {
        $current = $model;

        do {
            $name   = $current->getEntityModelName();
            $entity = $name ? $this->paramsHelper->getEntity($name) : null;
        } while (!$entity && $current = $this->tree->getParent($current));

        return $entity;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function detectLayoutCodename(IFaceModelInterface $model): string
    {
        $current = $model;

        // Climb up the IFace tree for a layout codename
        do {
            $layoutCodename = $current->getLayoutCodename();
        } while (!$layoutCodename && $current = $this->tree->getParent($current));

        if (!$layoutCodename) {
            throw new IFaceException('Cannot detect layout for iface :codename', [
                ':codename' => $model->getCodename(),
            ]);
        }

        return $layoutCodename;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return string
     */
    public function detectZoneName(IFaceModelInterface $model): string
    {
        $current = $model;

        do {
            $zoneName = $current->getZoneName();
            $current  = $this->tree->getParent($current);
        } while (!$zoneName && $current);

        // Public zone by default
        return $zoneName ?: IFaceZone::PUBLIC_ZONE;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getLabel(IFaceModelInterface $model): string
    {
        return $this->stringPatternHelper->processPattern($model->getLabel());
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getTitle(IFaceModelInterface $model): string
    {
        $title = $model->getTitle();

        if (!$title) {
            $title = $this->makeTitleFromLabels($model);
        }

        if (!$title) {
            throw new IFaceException('Can not compose title for IFace :codename', [
                ':codename' => $model->getCodename(),
            ]);
        }

        return $this->stringPatternHelper->processPattern($title, SeoMetaInterface::TITLE_LIMIT);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getDescription(IFaceModelInterface $model): string
    {
        $description = $model->getDescription();

        if (!$description) {
            // Suppress errors for empty description in admin zone
            return '';
        }

        return $this->stringPatternHelper->processPattern($description, SeoMetaInterface::DESCRIPTION_LIMIT);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function makeTitleFromLabels(IFaceModelInterface $model): string
    {
        $labels  = [];
        $current = $model;

        do {
            $labels[] = $this->getLabel($current);
            $current  = $this->tree->getParent($current);
        } while ($current);

        return implode(' - ', array_filter($labels));
    }
}
