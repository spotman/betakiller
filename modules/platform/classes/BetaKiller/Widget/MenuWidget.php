<?php
declare(strict_types=1);

namespace BetaKiller\Widget;

use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\I18nHelper;
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
     * @var \BetaKiller\Helper\UrlElementHelper
     */
    private $elementHelper;

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
        $user      = ServerRequestHelper::getUser($request);
        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $i18n      = ServerRequestHelper::getI18n($request);

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

        return $this->processLayer($iterator, $urlHelper, $i18n, $user, null);
    }

    /**
     * Processing IFace tree layer
     *
     * @param \RecursiveIterator                              $models
     * @param \BetaKiller\Helper\UrlHelper                    $urlHelper
     * @param \BetaKiller\Helper\I18nHelper                   $i18n
     * @param \BetaKiller\Model\UserInterface                 $user
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return array
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @link https://github.com/MarkBaker/GeneratorQuadTrees/blob/master/src/PointQuadTree.php
     */
    private function processLayer(
        \RecursiveIterator $models,
        UrlHelper $urlHelper,
        I18nHelper $i18n,
        UserInterface $user,
        ?UrlContainerInterface $params
    ): array {
        $items = [];

        // Iterate over every url element
        foreach ($models as $urlElement) {
            $params = $params ?: UrlContainer::create();

            // Iterate over every generated URL to make full tree
            foreach ($this->getAvailableIFaceUrls($urlElement, $params, $urlHelper) as $availableUrl) {
                // store parameter for childs processing
                $urlParameter = $availableUrl->getUrlParameter();

                if ($urlParameter) {
                    $params->setParameter($urlParameter, true);
                }
                try {
                    if (!$this->aclHelper->isUrlElementAllowed($user, $urlElement, $params)) {
                        continue;
                    }
                } catch (\Spotman\Acl\Exception $e) {
                    throw UrlElementException::wrap($e);
                }

                $item = [
                    'url'      => $availableUrl->getUrl(),
                    'label'    => $this->elementHelper->getLabel($urlElement, $params, $i18n),
                    'active'   => $urlHelper->inStack($urlElement, $params),
                    'children' => [],
                ];

                // recursion for children
                if ($models->hasChildren()) {
                    $item['children'] = $this->processLayer(
                        $models->getChildren(), $urlHelper, $i18n, $user, $params
                    );
                }

                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Generating URLs by IFace element
     *
     * @param \BetaKiller\Url\IFaceModelInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Helper\UrlHelper                    $helper
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function getAvailableIFaceUrls(
        IFaceModelInterface $model,
        UrlContainerInterface $params,
        UrlHelper $helper
    ): \Generator {
        $behaviour = $this->behaviourFactory->fromUrlElement($model);

        foreach ($behaviour->getAvailableUrls($model, $params, $helper) as $availableUrl) {
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
