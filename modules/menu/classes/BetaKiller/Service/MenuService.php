<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Acl\UrlElementAccessResolverInterface;
use BetaKiller\Exception\DomainException;
use BetaKiller\Factory\MenuCounterFactory;
use BetaKiller\Helper\TextHelper;
use BetaKiller\Helper\UrlElementHelper;
use BetaKiller\Menu\MenuItem;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use BetaKiller\Url\Container\ResolvingUrlContainer;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\ElementFilter\AggregateUrlElementFilter;
use BetaKiller\Url\ElementFilter\MenuCodenameUrlElementFilter;
use BetaKiller\Url\ElementFilter\UrlElementFilterInterface;
use BetaKiller\Url\UrlElementException;
use BetaKiller\Url\UrlElementForMenuInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementStack;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\UrlElementTreeRecursiveIterator;
use Generator;
use RecursiveIterator;
use Spotman\Acl\AclException;

class MenuService
{
    /**
     * @var \BetaKiller\Url\UrlElementStack
     */
    private $stack;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @var \BetaKiller\Url\Behaviour\UrlBehaviourFactory
     */
    private $behaviourFactory;

    /**
     * @var \BetaKiller\Helper\UrlElementHelper
     */
    private $elementHelper;

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
     * @var \BetaKiller\Factory\MenuCounterFactory
     */
    private $counterFactory;

    /**
     * @var \BetaKiller\Acl\UrlElementAccessResolverInterface
     */
    private $elementAccessResolver;

    /**
     * MenuService constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface           $tree
     * @param \BetaKiller\Acl\UrlElementAccessResolverInterface $elementAccessResolver
     * @param \BetaKiller\Helper\UrlElementHelper               $elementHelper
     * @param \BetaKiller\Factory\MenuCounterFactory            $counterFactory
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory     $behaviourFactory
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        UrlElementAccessResolverInterface $elementAccessResolver,
        UrlElementHelper $elementHelper,
        MenuCounterFactory $counterFactory,
        UrlBehaviourFactory $behaviourFactory
    ) {
        $this->tree                  = $tree;
        $this->behaviourFactory      = $behaviourFactory;
        $this->elementHelper         = $elementHelper;
        $this->counterFactory        = $counterFactory;
        $this->elementAccessResolver = $elementAccessResolver;
    }

    /**
     * @param string                                          $menuName
     * @param \BetaKiller\Model\UserInterface                 $user
     * @param int                                             $level Menu level to start (1, 2, 3, etc)
     * @param int                                             $depth Menu depth in levels (1, 2, 3, etc)
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParams
     * @param \BetaKiller\Url\UrlElementStack                 $stack
     *
     * @return \BetaKiller\Menu\MenuItem[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\I18n\I18nException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\ElementFilter\UrlElementFilterException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getItems(
        string $menuName,
        UserInterface $user,
        int $level,
        int $depth,
        UrlContainerInterface $urlParams,
        UrlElementStack $stack
    ): array {
        // Get all items with proper 'menu' attribute (merge in root)
        // If level is not null => get only items in provided depth filtered by current stack (search for items in result set)

        // Filter by IFace URL menu codename
        $this->filter = new AggregateUrlElementFilter([
            new MenuCodenameUrlElementFilter($menuName),
        ]);

        $this->startLevel = $level;
        $this->depth      = $depth;
        $this->user       = $user;

        $this->stack = $stack;

        // Iterate over all tree (filtering will be done later)
        $iterator = new UrlElementTreeRecursiveIterator($this->tree);

        // was null (new container on every branch)
        $rootParams = ResolvingUrlContainer::create();

        $rootParams->import($urlParams);

        // Start from 1 for simplicity
        $totalCounter = 1;

        // Generate menu items
        return $this->processLayer($iterator, $rootParams, $totalCounter);
    }

    /**
     * @param MenuItem[] $items
     *
     * @return string[][]
     */
    public function convertToJson(array $items): array
    {
        $data = [];

        foreach ($items as $item) {
            $data[] = $item->jsonSerialize();
        }

        return $data;
    }

    /**
     * Processing UrlElement tree layer
     *
     * @param \RecursiveIterator|UrlElementInterface[]        $iterator
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @param int|null                                        $totalCounter
     *
     * @return MenuItem[]
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Exception\DomainException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\I18n\I18nException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @link https://github.com/MarkBaker/GeneratorQuadTrees/blob/master/src/PointQuadTree.php
     */
    private function processLayer(
        RecursiveIterator $iterator,
        UrlContainerInterface $params,
        int &$totalCounter
    ): array {
        $items = [];

        // Iterate over every url element
        foreach ($iterator as $urlElement) {
            // Check current UrlElement is in menu
            $isInMenu = $this->filter->isAvailable($urlElement);

            if ($isInMenu) {
                // Increase level
                $this->currentLevel++;
            }

            $useChildren = $iterator->hasChildren(); // true by default
            $useCurrent  = true;

            if ($this->startLevel > 1 && $this->startLevel - $this->currentLevel === 1) {
                // Skip items on upper levels
                $useCurrent = false;

                // Skip branches which are not active right now (leveled menu depends on currently selected items)
                // Keep diving in if we are in menu branch already
                if ($isInMenu && !$this->stack->has($urlElement, $params)) {
                    $useChildren = false;
                }
            } elseif ($this->currentLevel < $this->startLevel) {
                // Skip items on upper levels but keep children
                $useCurrent = false;
            } elseif ($this->currentLevel >= $this->startLevel + $this->depth) {
                // Skip items on lower levels
                $useCurrent = false;

                // No child processing for nested levels
                if ($isInMenu) {
                    $useChildren = false;
                }
            } elseif ($isInMenu) {
                // No children injection for menu items (they would be inserted into current item)
                $useChildren = false;
            } else {
                // No current processing for non-menu items
                $useCurrent = false;
            }

//            d($this->currentLevel, $urlElement->getCodename(), $useCurrent, $useChildren);

            if ($useCurrent) {
                // Element is in menu, processing
                $orderedItemsBaseCounter = $totalCounter;
                $availableUrlCounter     = 0;

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

                    $url = $availableUrl->getUrl();

                    // Calculate menu counter if needed
                    $counter = $urlElement instanceof UrlElementForMenuInterface
                        ? $this->getMenuItemCounter($urlElement, $params)
                        : null;

                    // Calculate order
                    $itemOrder = $urlElement instanceof UrlElementForMenuInterface
                        ? $this->getMenuItemOrder($urlElement, $url)
                        : $availableUrlCounter;

                    // Use current URL if item is in menu
                    $item = new MenuItem(
                        $url,
                        $this->elementHelper->getLabel($urlElement, $params, $this->user->getLanguage()),
                        $this->stack->has($urlElement, $params),
                        $urlElement->getCodename(),
                        $counter,
                        $itemOrder + $orderedItemsBaseCounter
                    );

                    $items[] = $item;

                    // Recursion for children
                    if ($iterator->hasChildren()) {
                        $item->addChildren($this->processLayer($iterator->getChildren(), $params, $totalCounter));
                    }

                    $availableUrlCounter++;
                }

                $totalCounter++;
            } elseif ($useChildren) {
                // Just push children up, keep ordering
                foreach ($this->processLayer($iterator->getChildren(), $params, $totalCounter) as $item) {
                    $items[] = $item;
                }
            }

            if ($isInMenu) {
                // Decrease level
                $this->currentLevel--;
            }
        }

        // Sort items by order
        usort($items, static function (MenuItem $a, MenuItem $b) {
            return $a->getOrder() <=> $b->getOrder();
        });

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
            return $this->elementAccessResolver->isAllowed($this->user, $element, $params);
        } catch (AclException $e) {
            throw UrlElementException::wrap($e);
        }
    }

    private function getMenuItemOrder(UrlElementForMenuInterface $urlElement, string $url): ?int
    {
        // Dynamic UrlElement => order contains list of possible URL values (comma-separated)
        $orderedValues = $urlElement->getMenuOrder();

        // No order => nothing to do
        if (!$orderedValues) {
            return null;
        }

        if (!$urlElement->hasDynamicUrl()) {
            throw new DomainException('Only dynamic URLs may define custom menu order but ":name" used ":values"', [
                ':name'   => $urlElement->getCodename(),
                ':values' => \implode(', ', $orderedValues),
            ]);
        }

        foreach ($orderedValues as $index => $value) {
            if (TextHelper::endsWith($url, $value)) {
                return $index;
            }
        }

        throw new UrlElementException('Can not detect menu order for URL ":url"', [
            ':url' => $url,
        ]);
    }

    private function getMenuItemCounter(UrlElementForMenuInterface $urlElement, UrlContainerInterface $params): ?int
    {
        $codename = $urlElement->getMenuCounterCodename();

        if (!$codename) {
            return null;
        }

        return $this->counterFactory->create($codename)->getMenuCounter($params, $this->user);
    }
}
