<?php
namespace BetaKiller\Helper;

use BetaKiller\CrudlsActionsInterface;
use BetaKiller\Factory\IFaceFactory;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\EntityLinkedUrlElementInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementStack;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\ZoneInterface;
use Spotman\Api\ApiMethodResponse;

class IFaceHelper
{
    /**
     * @var \BetaKiller\Url\UrlElementStack
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
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @var \BetaKiller\Helper\StringPatternHelper
     */
    private $stringPatternHelper;

    /**
     * @var \BetaKiller\Factory\IFaceFactory
     */
    private $factory;

    /**
     * @var \BetaKiller\Helper\I18nHelper
     */
    private $i18n;

    /**
     * IFaceHelper constructor.
     *
     * @param \BetaKiller\Factory\IFaceFactory        $factory
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     * @param \BetaKiller\Url\UrlElementStack         $stack
     * @param \BetaKiller\Helper\UrlHelper            $urlHelper
     * @param \BetaKiller\Helper\UrlContainerHelper   $paramsHelper
     * @param \BetaKiller\Helper\StringPatternHelper  $stringPatternHelper
     * @param \BetaKiller\Helper\I18nHelper           $i18n
     */
    public function __construct(
        IFaceFactory $factory,
        UrlElementTreeInterface $tree,
        UrlElementStack $stack,
        UrlHelper $urlHelper,
        UrlContainerHelper $paramsHelper,
        StringPatternHelper $stringPatternHelper,
        I18nHelper $i18n
    ) {
        $this->stack               = $stack;
        $this->paramsHelper        = $paramsHelper;
        $this->urlHelper           = $urlHelper;
        $this->tree                = $tree;
        $this->stringPatternHelper = $stringPatternHelper;
        $this->factory             = $factory;
        $this->i18n                = $i18n;
    }

    /**
     * @return \BetaKiller\Url\IFaceModelInterface|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getCurrentIFaceModel(): ?IFaceModelInterface
    {
        $element = $this->stack->getCurrent();

        if ($element && !$element instanceof IFaceModelInterface) {
            throw new IFaceException('Current URL element :codename is not an IFace, :class given', [
                ':codename' => $element->getCodename(),
                ':class'    => \get_class($element),
            ]);
        }

        return $element;
    }

    public function getCurrentUrlElement(): ?UrlElementInterface
    {
        return $this->stack->getCurrent();
    }

    public function isCurrentIFaceAction(string $name): bool
    {
        $currentElement = $this->getCurrentIFaceModel();
        $currentAction  = $currentElement ? $currentElement->getEntityActionName() : null;

        return $currentAction === $name;
    }

    /**
     * @param string $zone
     *
     * @return bool
     */
    public function isCurrentZone(string $zone): bool
    {
        $currentIFace = $this->getCurrentIFaceModel();
        $currentZone  = $currentIFace ? $currentIFace->getZoneName() : null;

        return $currentZone === $zone;
    }

    public function isCurrentIFace(IFaceInterface $iface, UrlContainerInterface $params = null): bool
    {
        return $this->stack->isCurrent($iface->getModel(), $params);
    }

    public function inStack(UrlElementInterface $model): bool
    {
        return $this->stack->has($model);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function createIFaceFromCodename(string $codename): IFaceInterface
    {
        $urlElement = $this->getUrlElementByCodename($codename);

        return $this->factory->createFromUrlElement($urlElement);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function createIFaceFromUrlElement(UrlElementInterface $urlElement): IFaceInterface
    {
        return $this->factory->createFromUrlElement($urlElement);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getUrlElementByCodename(string $codename): UrlElementInterface
    {
        return $this->tree->getByCodename($codename);
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
                throw new IFaceException('UrlElement zone must be specified');
            }

            // Fetch zone from current UrlElement
            $zone = $currentIFaceModel->getZoneName();
        }

        $params = $this->paramsHelper->createResolving();
        $params->setParameter($entity);

        // Search for URL element with provided entity, action and zone
        $urlElement = $this->tree->getByEntityActionAndZone($entity, $action, $zone);

        return $this->makeUrl($urlElement, $params, $removeCycling);
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
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_READ, ZoneInterface::PREVIEW);
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
     * @param \BetaKiller\Url\UrlElementInterface                  $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     * @param bool|null                                            $removeCyclingLinks
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function makeUrl(
        UrlElementInterface $urlElement,
        ?UrlContainerInterface $params = null,
        ?bool $removeCyclingLinks = null
    ): string {
        return $this->urlHelper->makeUrl($urlElement, $params, $removeCyclingLinks);
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface                     $iface
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     * @param bool|null                                            $removeCyclingLinks
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
     * @param \BetaKiller\Url\EntityLinkedUrlElementInterface $urlElement
     *
     * @return \BetaKiller\Model\DispatchableEntityInterface|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function detectPrimaryEntity(EntityLinkedUrlElementInterface $urlElement): ?DispatchableEntityInterface
    {
        $current = $urlElement;

        do {
            $name   = $current->getEntityModelName();
            $entity = $name ? $this->paramsHelper->getEntity($name) : null;
        } while (!$entity && $current = $this->tree->getParent($current));

        return $entity;
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface $model
     *
     * @return string|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function detectLayoutCodename(IFaceModelInterface $model): ?string
    {
        $current = $model;

        // Climb up the IFace tree for a layout codename
        do {
            $layoutCodename = $current->getLayoutCodename();
        } while (!$layoutCodename && $current = $this->tree->getParentIFaceModel($current));

        return $layoutCodename;
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface                  $model
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     * @param int|null                                             $limit
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getLabel(
        IFaceModelInterface $model,
        ?UrlContainerInterface $params = null,
        ?int $limit = null
    ): string {
        $label = $model->getLabel();

        if (!$label) {
            throw new IFaceException('Missing label for :codename UrlElement', [':codename' => $model->getCodename()]);
        }

        if ($this->i18n->isI18nKey($label)) {
            $label = __($label);
        }

        return $this->stringPatternHelper->processPattern($label, $limit, $params);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface $model
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
     * @param \BetaKiller\Url\IFaceModelInterface $model
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
     * @param \BetaKiller\Url\IFaceModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
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
