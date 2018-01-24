<?php
namespace BetaKiller\Url;


use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;

class AvailableUrlsCollector
{
    use LoggerHelperTrait;

    /**
     * @Inject
     * @var \BetaKiller\IFace\IFaceModelTree
     */
    private $tree;

    /**
     * @Inject
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

    /**
     * @var \BetaKiller\Url\UrlPrototypeService
     */
    private $prototypeService;

    /**
     * @Inject
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * @Inject
     * @var \BetaKiller\Url\Behaviour\UrlBehaviourFactory
     */
    private $behaviourFactory;

    /**
     * AvailableUrlsCollector constructor.
     *
     * @param \BetaKiller\Helper\AclHelper        $aclHelper
     * @param \BetaKiller\Url\UrlPrototypeService $prototypeHelper
     */
    public function __construct(
        AclHelper $aclHelper,
        UrlPrototypeService $prototypeHelper
    ) {
        $this->aclHelper        = $aclHelper;
        $this->prototypeService = $prototypeHelper;
    }

    // TODO Extract logic from IFaceHelper
    // Implement IFaceTree traversing
    // For each iface get collection of AvailableUri objects, convert them into URLs and yield them

    /** @link https://github.com/MarkBaker/GeneratorQuadTrees/blob/master/src/PointQuadTree.php */

    /**
     * @TODO Move to AvailableUrlsCollector
     *
     * @todo This method calculates only urls for current iface but pair "uri => urlParameter" is needed for traversing over url tree and creating full url map
     *
     * @param bool|null $useHidden
     *
     * @return string[]|\Generator
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getPublicAvailableUrls(?bool $useHidden = null): \Generator
    {
        $useHidden = $useHidden ?? false;

        $params = new UrlContainer();

        $root = $this->tree->getRoot();

        foreach ($this->processLayer($root, $params, $useHidden) as $item) {
            yield $item->getUrl();
        }
    }

    /**
     * @param IFaceModelInterface[]                                 $models
     * @param \BetaKiller\Url\UrlContainerInterface $params
     * @param bool|null                             $useHidden
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     */
    private function processLayer(array $models, UrlContainerInterface $params, ?bool $useHidden = null): \Generator
    {
        foreach ($models as $ifaceModel) {
            // Skip hidden ifaces
            if (!$useHidden && $ifaceModel->hideInSiteMap()) {
                continue;
            }

            $childs = $this->tree->getChildren($ifaceModel);

            foreach ($this->getAvailableIFaceUrls($ifaceModel, $params) as $availableUrl) {
                $urlParameter = $availableUrl->getUrlParameter();

                // Store parameter for childs processing
                if ($urlParameter) {
                    $params->setParameter($urlParameter);
                }

                if (!$this->aclHelper->isIFaceAllowed($ifaceModel, $params)) {
                    continue;
                }

                yield $availableUrl;

                // Recursion for childs
                foreach ($this->processLayer($childs, $params, $useHidden) as $childAvailableUrl) {
                    yield $childAvailableUrl;
                }
            }
        }
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     * @param \BetaKiller\Url\UrlContainerInterface $params
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     */
    private function getAvailableIFaceUrls(IFaceModelInterface $model, UrlContainerInterface $params): \Generator
    {
        $behaviour = $this->behaviourFactory->fromIFaceModel($model);

        foreach ($behaviour->getAvailableUrls($model, $params) as $availableUrl) {
            yield $availableUrl;
        }
    }

//    /**
//     * @param \BetaKiller\IFace\IFaceInterface      $iface
//     * @param \BetaKiller\Url\UrlContainerInterface $params
//     *
//     * @return array
//     * @throws \BetaKiller\IFace\Exception\IFaceException
//     * @throws \BetaKiller\Url\UrlPrototypeException
//     * @throws \Spotman\Acl\Exception
//     */
//    private function getPublicAvailableUrlsForIFace(IFaceInterface $iface, UrlContainerInterface $params): array
//    {
//        if (!$iface->getModel()->hasDynamicUrl()) {
//            // Make static URL
//            return [$this->makeAvailableUrl($iface, $params)];
//        }
//
//        return $this->getDynamicModelAvailableUrls($iface, $params);
//    }
//
//    /**
//     * @param \BetaKiller\IFace\IFaceInterface      $iface
//     * @param \BetaKiller\Url\UrlContainerInterface $params
//     *
//     * @return string[]
//     * @throws \Spotman\Acl\Exception
//     * @throws \BetaKiller\IFace\Exception\IFaceException
//     * @throws \BetaKiller\Url\UrlPrototypeException
//     */
//    private function getDynamicModelAvailableUrls(IFaceInterface $iface, UrlContainerInterface $params): array
//    {
//        $prototype  = $this->prototypeService->createPrototypeFromIFaceModel($iface->getModel());
//        $dataSource = $this->prototypeService->getDataSourceInstance($prototype);
//
//        $this->prototypeService->validatePrototypeModelKey($prototype, $dataSource);
//
//        $urls = [];
//
//        $this->collectDataSourceAvailableUrls($iface, $prototype, $params, $urls);
//
//        return array_filter($urls);
//    }
//
//    /**
//     * @param \BetaKiller\IFace\IFaceInterface      $iface
//     * @param \BetaKiller\Url\UrlPrototype          $prototype
//     * @param \BetaKiller\Url\UrlContainerInterface $params
//     *
//     * @return \Generator
//     * @throws \BetaKiller\Factory\FactoryException
//     * @throws \BetaKiller\IFace\Exception\IFaceException
//     * @throws \BetaKiller\Url\UrlPrototypeException
//     * @throws \Spotman\Acl\Exception
//     */
//    private function collectDataSourceAvailableUrls(
//        IFaceInterface $iface,
//        UrlPrototype $prototype,
//        UrlContainerInterface $params
//    ): \Generator {
//        foreach ($items as $item) {
//            // Save current item to parameters registry
//            $params->setParameter($item, true);
//
//            // Make dynamic URL
//            $url = $this->makeAvailableUrl($iface, $params);
//
//            if (!$url) {
//                // No tree traversal if current url is not allowed
//                continue;
//            }
//
//            yield $url;
//
//            // Recursion for trees
//            if ($iface->getModel()->hasTreeBehaviour()) {
//                // Recursion for tree behaviour
//                foreach ($this->collectDataSourceAvailableUrls($iface, $prototype, $params) as $url) {
//                    yield $url;
//                }
//            }
//        }
//    }
//
//    /**
//     * @param \BetaKiller\IFace\IFaceInterface           $iface
//     * @param \BetaKiller\Url\UrlContainerInterface|null $params
//     *
//     * @return null|string
//     * @throws \BetaKiller\Factory\FactoryException
//     * @throws \BetaKiller\IFace\Exception\IFaceException
//     * @throws \Spotman\Acl\Exception
//     */
//    private function makeAvailableUrl(IFaceInterface $iface, ?UrlContainerInterface $params = null): ?string
//    {
//        if (!$this->aclHelper->isIFaceAllowed($iface, $params)) {
//            return null;
//        }
//
//        return $this->urlHelper->makeIFaceUrl($iface, $params, false); // Disable cycling links removing
//    }
}
