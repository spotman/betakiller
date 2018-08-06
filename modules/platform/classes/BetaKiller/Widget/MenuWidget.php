<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementFilters;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\UrlElementTreeRecursiveIterator;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\Container\UrlContainer;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\IFaceHelper;

class MenuWidget extends AbstractPublicWidget
{
    /**
     * @var ConfigProviderInterface
     */
    private $config;

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
     * @param \BetaKiller\Config\ConfigProviderInterface    $config
     * @param \BetaKiller\Url\UrlElementTreeInterface       $tree
     * @param \BetaKiller\Helper\IFaceHelper                $ifaceHelper
     * @param \BetaKiller\Helper\AclHelper                  $aclHelper
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory $behaviourFactory
     */
    public function __construct(
        ConfigProviderInterface $config,
        UrlElementTreeInterface $tree,
        IFaceHelper $ifaceHelper,
        AclHelper $aclHelper,
        UrlBehaviourFactory $behaviourFactory
    ) {
        $this->config           = $config;
        $this->tree             = $tree;
        $this->ifaceHelper      = $ifaceHelper;
        $this->aclHelper        = $aclHelper;
        $this->behaviourFactory = $behaviourFactory;
    }

    /**
     * Returns data for View rendering: menu links
     *
     * @return array [[string url, string label, bool active, array children], ...]
     * @throws \BetaKiller\Widget\MenuFilterInvalidNameException
     */
    public function getData(): array
    {
        // creating filters
        $filters  = null;
        $context  = $this->getContext();
        $codename = mb_strtolower(trim($context['menu']));
        if ($codename !== '') {
            $filterCodename = new MenuFilterCodename($codename);
            $filters        = new UrlElementFilters();
            $filters->addFilter($filterCodename);
        }

        // generation items of links menu
        $items    = [];
        $iterator = new UrlElementTreeRecursiveIterator($this->tree, null, $filters);
        foreach ($this->processLayer($iterator, null) as $item) {
            $items = $item;
        }

        return $items;
    }

    /**
     * Processing IFace tree layer
     *
     * @param \RecursiveIterator                              $models
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function processLayer(
        \RecursiveIterator $models,
        ?UrlContainerInterface $params = null
    ): \Generator {
        foreach ($models as $urlElement) {
            $params = $params ?: new UrlContainer();
            foreach ($this->processSingle($models, $urlElement, $params) as $item) {
                yield $item;
            }
        }
    }

    /**
     * Processing IFace tree item
     *
     * @param \RecursiveIterator                              $models
     * @param \BetaKiller\Url\UrlElementInterface             $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return \Generator
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     *
     * @link https://github.com/MarkBaker/GeneratorQuadTrees/blob/master/src/PointQuadTree.php
     */
    private function processSingle(
        \RecursiveIterator $models,
        UrlElementInterface $urlElement,
        UrlContainerInterface $params
    ): \Generator {
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

            // item data
            $iface  = $this->ifaceHelper->createIFaceFromCodename($urlElement->getCodename());
            $url    = $this->ifaceHelper->makeIFaceUrl($iface, $params, false);
            $label  = $this->ifaceHelper->getLabel($iface->getModel(), $params);
            $active = $this->ifaceHelper->isCurrentIFace($iface, $params);

            $result = [
                'url'      => $url,
                'label'    => $label,
                'active'   => $active,
                'children' => [],
            ];

            // recursion for children
            if ($models->hasChildren()) {
                $modelsChildren = $models->getChildren();
                foreach ($this->processLayer($modelsChildren, $params) as $resultChildren) {
                    $result['children'][] = $resultChildren;
                }
            }

            yield $result;
        }
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

        // TODO Deal with calculation of the last_modified from each parameter value

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
