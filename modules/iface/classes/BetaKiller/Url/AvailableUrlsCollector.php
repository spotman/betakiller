<?php
namespace BetaKiller\Url;


use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\IFaceInterface;

class AvailableUrlsCollector
{
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
     * @var IFaceHelper
     */
    private $ifaceHelper;

    /**
     * @var \BetaKiller\Url\UrlPrototypeHelper
     */
    private $prototypeHelper;

    /**
     * AvailableUrlsCollector constructor.
     *
     * @param \BetaKiller\Helper\AclHelper       $aclHelper
     * @param \BetaKiller\Helper\IFaceHelper     $ifaceHelper
     * @param \BetaKiller\Url\UrlPrototypeHelper $prototypeHelper
     */
    public function __construct(
        AclHelper $aclHelper,
        IFaceHelper $ifaceHelper,
        UrlPrototypeHelper $prototypeHelper
    ) {
        $this->aclHelper       = $aclHelper;
        $this->ifaceHelper     = $ifaceHelper;
        $this->prototypeHelper = $prototypeHelper;
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
     * @return string[]
     */
    public function getPublicAvailableUrls(?bool $useHidden = null): array
    {
        $useHidden = $useHidden ?? false;

        // Get all ifaces recursively
        $iterator = $this->tree->getRecursivePublicIterator();

        $params = new UrlContainer();

        // For each IFace
        foreach ($iterator as $ifaceModel) {
            // Skip hidden ifaces
            if (!$useHidden && $ifaceModel->hideInSiteMap()) {
                continue;
            }

            $this->logger->debug('Found IFace :codename', [':codename' => $ifaceModel->getCodename()]);

            try {
                $this->getPublicAvailableUrlsForIFace($ifaceModel, $params);
            } catch (\Throwable $e) {
                $this->logger->warning('Exception thrown for :iface with message :text', [
                    ':iface' => $ifaceModel->getCodename(),
                    ':text'  => $e->getMessage(),
                ]);
            }
        }

        // TODO
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface      $iface
     * @param \BetaKiller\Url\UrlContainerInterface $params
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \Spotman\Acl\Exception
     */
    private function getPublicAvailableUrlsForIFace(IFaceInterface $iface, UrlContainerInterface $params): array
    {
        if (!$iface->getModel()->hasDynamicUrl()) {
            // Make static URL
            return [$this->makeAvailableUrl($iface, $params)];
        }

        return $this->getDynamicModelAvailableUrls($iface, $params);
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface      $iface
     * @param \BetaKiller\Url\UrlContainerInterface $params
     *
     * @return string[]
     * @throws \Spotman\Acl\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    private function getDynamicModelAvailableUrls(IFaceInterface $iface, UrlContainerInterface $params): array
    {
        $prototype  = $this->prototypeHelper->fromIFaceUri($iface);
        $dataSource = $this->prototypeHelper->getDataSourceInstance($prototype);

        $this->prototypeHelper->validatePrototypeModelKey($prototype, $dataSource);

        $urls = [];

        $this->collectDataSourceAvailableUrls($iface, $dataSource, $prototype, $params, $urls);

        return array_filter($urls);
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface       $iface
     * @param \BetaKiller\Url\UrlDataSourceInterface $dataSource
     * @param \BetaKiller\Url\UrlPrototype           $prototype
     * @param \BetaKiller\Url\UrlContainerInterface  $params
     * @param array                                  $urls
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    private function collectDataSourceAvailableUrls(
        IFaceInterface $iface,
        UrlDataSourceInterface $dataSource,
        UrlPrototype $prototype,
        UrlContainerInterface $params,
        array &$urls
    ): void {
        $items = $prototype->hasIdKey()
            ? $dataSource->getAll()
            : $dataSource->getItemsHavingUrlKey($params);

        foreach ($items as $item) {
            // Save current item to parameters registry
            $params->setParameter($item, true);

            // Make dynamic URL
            $url = $this->makeAvailableUrl($iface, $params);

            if (!$url) {
                // No tree traversal if current url is not allowed
                continue;
            }

            $urls[] = $url;

            // Recursion for trees
            if ($iface->getModel()->hasTreeBehaviour()) {
                // Recursion for tree behaviour
                $this->collectDataSourceAvailableUrls($iface, $dataSource, $prototype, $params, $urls);
            }
        }
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface           $iface
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     *
     * @return null|string
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    private function makeAvailableUrl(IFaceInterface $iface, UrlContainerInterface $params = null): ?string
    {
        if (!$this->aclHelper->isIFaceAllowed($iface, $params)) {
            return null;
        }

        return $this->ifaceHelper->makeUrl($iface, $params, false); // Disable cycling links removing
    }
}
