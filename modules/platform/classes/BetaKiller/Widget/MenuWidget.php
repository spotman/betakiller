<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use BetaKiller\Url\Container\UrlContainer;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\ElementFilter\AggregateUrlElementFilter;
use BetaKiller\Url\ElementFilter\MenuCodenameUrlElementFilter;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\UrlElementTreeRecursiveIterator;

class MenuWidget extends AbstractPublicWidget
{
    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $behaviourFactory;

    /**
     * AuthWidget constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface       $tree
     * @param \BetaKiller\Helper\IFaceHelper                $ifaceHelper
     * @param \BetaKiller\Helper\AclHelper                  $aclHelper
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory $behaviourFactory
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        IFaceHelper $ifaceHelper,
        AclHelper $aclHelper,
        UrlBehaviourFactory $behaviourFactory
    ) {
        $this->tree             = $tree;
        $this->ifaceHelper      = $ifaceHelper;
        $this->aclHelper        = $aclHelper;
        $this->behaviourFactory = $behaviourFactory;
    }

    /**
     * Returns data for View rendering: menu links
     *
     * @return array [[string url, string label, bool active, array children], ...]
     * @throws \BetaKiller\Url\ElementFilter\UrlElementFilterException
     */
    public function getData(): array
    {
        // Menu codename from widget context
        $menuCodename   = trim($this->getContextParam('menu'));
        $parentCodename = trim($this->getContextParam('parent'));

        // Filter by IFace URL menu codename
        $filters = new AggregateUrlElementFilter(
            new MenuCodenameUrlElementFilter($menuCodename)
        );

        // Parent IFace URL element
        $parent = $parentCodename
            ? $this->tree->getByCodename($parentCodename)
            : null;

        // Generate menu items
        $iterator = new UrlElementTreeRecursiveIterator($this->tree, $parent, $filters);

        return $this->processLayer($iterator, null);
    }

    /**
     * Processing IFace tree layer
     *
     * @param \RecursiveIterator                              $models
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return array
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function processLayer(\RecursiveIterator $models, ?UrlContainerInterface $params = null): array
    {
        $items = [];

        foreach ($models as $urlElement) {
            $params   = $params ?: new UrlContainer();
            $itemsAdd = $this->processSingle($models, $urlElement, $params);
            if (!$itemsAdd) {
                continue;
            }
            foreach ($itemsAdd as $itemsAddItem) {
                $items[] = $itemsAddItem;
            }
        }

        return $items;
    }

    /**
     * Processing IFace tree item
     *
     * @param \RecursiveIterator                              $models
     * @param \BetaKiller\Url\UrlElementInterface             $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return array
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     *
     * @link https://github.com/MarkBaker/GeneratorQuadTrees/blob/master/src/PointQuadTree.php
     */
    private function processSingle(
        \RecursiveIterator $models,
        UrlElementInterface $urlElement,
        UrlContainerInterface $params
    ): array {
        $result = [];

        foreach ($this->getAvailableIFaceUrls($urlElement, $params) as $availableUrl) {
            // store parameter for childs processing
            $urlParameter = $availableUrl->getUrlParameter();
            if ($urlParameter) {
                $params->setParameter($urlParameter, true);
            }
            try {
                if (!$this->aclHelper->isUrlElementAllowed($urlElement, $params)) {
                    continue;
                }
            } catch (\Spotman\Acl\Exception $e) {
                throw IFaceException::wrap($e);
            }

            // Item data
            $iface = $this->ifaceHelper->createIFaceFromCodename($urlElement->getCodename());

            $resultItem = [
                'url'      => $this->ifaceHelper->makeIFaceUrl($iface, $params, false),
                'label'    => $this->ifaceHelper->getLabel($iface->getModel(), $params),
                'active'   => $this->ifaceHelper->isCurrentIFace($iface, $params),
                'children' => [],
            ];

            // recursion for children
            if ($models->hasChildren()) {
                $resultItem['children'] = $this->processLayer($models->getChildren(), $params);
            }

            $result[] = $resultItem;
        }

        return $result;
    }

    /**
     * Generating URLs by IFace element
     *
     * @param \BetaKiller\Url\UrlElementInterface             $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function getAvailableIFaceUrls(UrlElementInterface $urlElement, UrlContainerInterface $params): \Generator
    {
        $behaviour = $this->behaviourFactory->fromUrlElement($urlElement);

        foreach ($behaviour->getAvailableUrls($urlElement, $params) as $availableUrl) {
            yield $availableUrl;
        }
    }

    /**
     * Returns true if current widget may be omitted during the render process
     *
     * @return bool
     */
    public function isEmptyResponseAllowed(): bool
    {
        // Always visible
        return false;
    }
}
