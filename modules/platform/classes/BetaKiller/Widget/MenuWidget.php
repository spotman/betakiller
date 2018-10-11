<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlElementHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use BetaKiller\Url\Container\UrlContainer;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\ElementFilter\AggregateUrlElementFilter;
use BetaKiller\Url\ElementFilter\MenuCodenameUrlElementFilter;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\UrlElementTreeRecursiveIterator;
use Psr\Http\Message\ServerRequestInterface;

class MenuWidget extends AbstractPublicWidget
{
    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

    /**
     * @var \BetaKiller\Url\Behaviour\UrlBehaviourFactory
     */
    private $behaviourFactory;

    /**
     * AuthWidget constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface       $tree
     * @param \BetaKiller\Helper\AclHelper                  $aclHelper
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory $behaviourFactory
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        AclHelper $aclHelper,
        UrlBehaviourFactory $behaviourFactory
    ) {
        $this->tree             = $tree;
        $this->aclHelper        = $aclHelper;
        $this->behaviourFactory = $behaviourFactory;
    }

    /**
     * Returns data for View rendering: menu links
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @param array                                    $context
     *
     * @return array [[string url, string label, bool active, array children], ...]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\ElementFilter\UrlElementFilterException
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $user          = ServerRequestHelper::getUser($request);
        $urlHelper     = ServerRequestHelper::getUrlHelper($request);
        $elementHelper = ServerRequestHelper::getUrlElementHelper($request);

        // Menu codename from widget context
        $menuCodename   = (string)$context['menu'];
        $parentCodename = (string)($context['parent'] ?? null);

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

        return $this->processLayer($iterator, $elementHelper, $urlHelper, $user, null);
    }

    /**
     * Processing IFace tree layer
     *
     * @param \RecursiveIterator                              $models
     * @param \BetaKiller\Helper\UrlElementHelper             $elementHelper
     * @param \BetaKiller\Helper\UrlHelper                    $urlHelper
     * @param \BetaKiller\Model\UserInterface                 $user
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return array
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    private function processLayer(
        \RecursiveIterator $models,
        UrlElementHelper $elementHelper,
        UrlHelper $urlHelper,
        UserInterface $user,
        ?UrlContainerInterface $params = null
    ): array {
        $items = [];

        foreach ($models as $urlElement) {
            $params = $params ?: UrlContainer::create();

            foreach ($this->processSingle($models, $urlElement, $params, $elementHelper, $urlHelper, $user) as $item) {
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
     * @param \BetaKiller\Helper\UrlElementHelper             $elementHelper
     * @param \BetaKiller\Helper\UrlHelper                    $urlHelper
     * @param \BetaKiller\Model\UserInterface                 $user
     *
     * @return array
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @link https://github.com/MarkBaker/GeneratorQuadTrees/blob/master/src/PointQuadTree.php
     */
    private function processSingle(
        \RecursiveIterator $models,
        IFaceModelInterface $ifaceModel,
        UrlContainerInterface $params,
        UrlElementHelper $elementHelper,
        UrlHelper $urlHelper,
        UserInterface $user
    ): array {
        $result = [];

        foreach ($this->getAvailableIFaceUrls($ifaceModel, $params) as $availableUrl) {
            // store parameter for childs processing
            $urlParameter = $availableUrl->getUrlParameter();

            if ($urlParameter) {
                $params->setParameter($urlParameter, true);
            }
            try {
                if (!$this->aclHelper->isUrlElementAllowed($user, $ifaceModel, $params)) {
                    continue;
                }
            } catch (\Spotman\Acl\Exception $e) {
                throw UrlElementException::wrap($e);
            }

            $resultItem = [
                'url'      => $urlHelper->makeUrl($ifaceModel, $params, false),
                'label'    => $elementHelper->getLabel($ifaceModel, $params),
                'active'   => $urlHelper->inStack($ifaceModel, $params),
                'children' => [],
            ];

            // recursion for children
            if ($models->hasChildren()) {
                $resultItem['children'] = $this->processLayer(
                    $models->getChildren(),
                    $elementHelper,
                    $urlHelper,
                    $user,
                    $params
                );
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
