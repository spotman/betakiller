<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\UrlElementHelper;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Menu\MenuItem;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\ElementFilter\AggregateUrlElementFilter;
use BetaKiller\Url\ElementFilter\MenuCodenameUrlElementFilter;
use BetaKiller\Url\ElementFilter\UrlElementFilterInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\UrlElementTreeRecursiveIterator;
use Generator;
use RecursiveIterator;

class MenuService
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
     * @var \BetaKiller\Helper\UrlElementHelper
     */
    private $elementHelper;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var UrlElementFilterInterface
     */
    private $filter;

    /**
     * @var int
     */
    private $currentLevel = 0;

    /**
     * @var int
     */
    private $startLevel;

    /**
     * @var int
     */
    private $depth;

    /**
     * AuthWidget constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface       $tree
     * @param \BetaKiller\Helper\AclHelper                  $aclHelper
     * @param \BetaKiller\Helper\UrlElementHelper           $elementHelper
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory $behaviourFactory
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        AclHelper $aclHelper,
        UrlElementHelper $elementHelper,
        UrlBehaviourFactory $behaviourFactory
    ) {
        $this->tree             = $tree;
        $this->aclHelper        = $aclHelper;
        $this->behaviourFactory = $behaviourFactory;
        $this->elementHelper    = $elementHelper;
    }

    /**
     * @param string                          $menuName
     * @param \BetaKiller\Helper\UrlHelper    $urlHelper
     * @param \BetaKiller\Model\UserInterface $user
     * @param int                             $level Menu level to start (1, 2, 3, etc)
     * @param int                             $depth Menu depth in levels (1, 2, 3, etc)
     *
     * @return \BetaKiller\Menu\MenuItem[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\I18n\I18nException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\ElementFilter\UrlElementFilterException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getItems(string $menuName, UrlHelper $urlHelper, UserInterface $user, int $level, int $depth): array
    {
        // Get all items with proper 'menu' attribute (merge in root)
        // If level is not null => get only items in provided depth filtered by current stack (search for items in result set)

        // Filter by IFace URL menu codename
        $this->filter = new AggregateUrlElementFilter([
            new MenuCodenameUrlElementFilter($menuName),
        ]);

        $this->startLevel = $level;
        $this->depth      = $depth;
        $this->urlHelper  = $urlHelper;
        $this->user       = $user;

        // Iterate over all tree (filtering will be done later)
        $iterator = new UrlElementTreeRecursiveIterator($this->tree);

        $rootParams = $urlHelper->createUrlContainer();

        $rootParams->import($urlHelper->getUrlContainer());

        // Generate menu items
        return $this->processLayer($iterator, $rootParams); // was null (new container on every branch)
    }

    /**
     * Processing UrlElement tree layer
     *
     * @param \RecursiveIterator|UrlElementInterface[]        $iterator
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return MenuItem[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\I18n\I18nException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @link https://github.com/MarkBaker/GeneratorQuadTrees/blob/master/src/PointQuadTree.php
     */
    private function processLayer(
        RecursiveIterator $iterator,
        UrlContainerInterface $params
    ): array {
        $items = [];

        // Iterate over every url element
        foreach ($iterator as $urlElement) {
            //$params = $params ?: $this->urlHelper->createUrlContainer();

            // Check current UrlElement is in menu
            $isInMenu = $this->filter->isAvailable($urlElement);

            if ($isInMenu) {
                // Increase level
                $this->currentLevel++;
            }

            $useChildren = $iterator->hasChildren(); // true by default
            $useCurrent  = true;

            if ($this->currentLevel < $this->startLevel) {
                // Skip items on upper levels
                $useCurrent = false;

                // Skip branches which are not active right now (leveled menu depends on currently selected items)
                if (!$this->urlHelper->inStack($urlElement, $params)) {
                    $useChildren = false;
                }
                //
            } else if ($this->currentLevel >= $this->startLevel + $this->depth) {
                // Skip items on lower levels
                $useCurrent = false;

                // No child processing for nested levels
                if ($isInMenu) {
                    $useChildren = false;
                }
            } else {
                // Menu zone

                // No child processing for nested levels
                if (!$isInMenu) {
                    $useCurrent = false;
                }
            }

//            d($this->currentLevel, $urlElement->getCodename(), $useCurrent, $useChildren);

            if ($useCurrent) {
                // Element is in menu, processing

                // Iterate over every generated URL to make full tree
                foreach ($this->getAvailableIFaceUrls($urlElement, $params) as $availableUrl) {
                    // Store parameter for childs processing (if exists)
                    $urlParameter = $availableUrl->getUrlParameter();

                    if ($urlParameter) {
                        $params->setParameter($urlParameter, true);
                    }

                    // Security check
                    if (!$this->isAllowed($urlElement, $params)) {
                        // Skip available URL for current UrlElement
                        continue;
                    }

                    // Recursion for children
                    $children = $iterator->hasChildren()
                        ? $this->processLayer($iterator->getChildren(), $params)
                        : [];

                    // Use current URL if item is in menu
                    $item = new MenuItem(
                        $availableUrl->getUrl(),
                        $this->elementHelper->getLabel($urlElement, $params, $this->user->getLanguage()),
                        $this->urlHelper->inStack($urlElement, $params)
                    );

                    $item->addChildren($children);

                    $items[] = $item;
                }
            }

            if ($useChildren) {
                // Just push children up
                foreach ($this->processLayer($iterator->getChildren(), $params) as $item) {
                    $items[] = $item;
                }
            }

            if ($isInMenu) {
                // Decrease level
                $this->currentLevel--;
            }
        }

        return $items;
    }

    /**
     * Generating URLs by IFace element
     *
     * @param \BetaKiller\Url\UrlElementInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function getAvailableIFaceUrls(
        UrlElementInterface $model,
        UrlContainerInterface $params
    ): Generator {
        $behaviour = $this->behaviourFactory->fromUrlElement($model);

        yield from $behaviour->getAvailableUrls($model, $params);
    }

    private function isAllowed(UrlElementInterface $element, UrlContainerInterface $params): bool
    {
        try {
            // Security check
            return $this->aclHelper->isUrlElementAllowed($this->user, $element, $params);
        } catch (\Spotman\Acl\Exception $e) {
            throw UrlElementException::wrap($e);
        }
    }
}
