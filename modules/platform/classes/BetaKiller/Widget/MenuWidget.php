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
use BetaKiller\Url\IFaceModelInterface;
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
        $menuCodename   = trim((string)$this->getContextParam('menu'));
        $parentCodename = trim((string)$this->getContextParam('parent'));

        // Filter by IFace URL menu codename
        $filters = new AggregateUrlElementFilter([
            new MenuCodenameUrlElementFilter($menuCodename),
        ]);

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
            $params = $params ?: UrlContainer::create();

            foreach ($this->processSingle($models, $urlElement, $params) as $item) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Processing IFace tree item
     *
     * @param \RecursiveIterator                              $models
     * @param \BetaKiller\Url\IFaceModelInterface             $ifaceModel
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
        IFaceModelInterface $ifaceModel,
        UrlContainerInterface $params
    ): array {
        $result = [];

        foreach ($this->getAvailableIFaceUrls($ifaceModel, $params) as $availableUrl) {
            // store parameter for childs processing
            $urlParameter = $availableUrl->getUrlParameter();

            if ($urlParameter) {
                $params->setParameter($urlParameter, true);
            }
            try {
                if (!$this->aclHelper->isUrlElementAllowed($ifaceModel, $params)) {
                    continue;
                }
            } catch (\Spotman\Acl\Exception $e) {
                throw IFaceException::wrap($e);
            }

            $resultItem = [
                'url'      => $this->ifaceHelper->makeUrl($ifaceModel, $params, false),
                'label'    => $this->ifaceHelper->getLabel($ifaceModel, $params),
                'active'   => $this->ifaceHelper->inStack($ifaceModel, $params),
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
     * @param \BetaKiller\Url\IFaceModelInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function getAvailableIFaceUrls(IFaceModelInterface $model, UrlContainerInterface $params): \Generator
    {
        $behaviour = $this->behaviourFactory->fromUrlElement($model);

        foreach ($behaviour->getAvailableUrls($model, $params) as $availableUrl) {
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
