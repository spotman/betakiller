<?php
namespace BetaKiller\Url;

use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceModelTree;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;

class AvailableUrlsCollector
{
    use LoggerHelperTrait;

    /**
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
     * @var \BetaKiller\Url\Behaviour\UrlBehaviourFactory
     */
    private $behaviourFactory;

    /**
     * AvailableUrlsCollector constructor.
     *
     * @param \BetaKiller\IFace\IFaceModelTree              $tree
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory $behaviourFactory
     * @param \BetaKiller\Helper\AclHelper                  $aclHelper
     */
    public function __construct(
        IFaceModelTree $tree,
        UrlBehaviourFactory $behaviourFactory,
        AclHelper $aclHelper
    ) {
        $this->tree             = $tree;
        $this->aclHelper        = $aclHelper;
        $this->behaviourFactory = $behaviourFactory;
    }

    /**
     * @param bool|null $useHidden
     *
     * @return string[]|\Generator
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getPublicAvailableUrls(?bool $useHidden = null): \Generator
    {
        $useHidden = $useHidden ?? false;

        $root = $this->tree->getRoot();

        // Use empty UrlContainer on each root IFace iteration (so no intersection of models between paths)
        foreach ($this->processLayer($root, null, $useHidden) as $item) {
            yield $item->getUrl();
        }
    }

    /**
     * @param IFaceModelInterface[]                 $models
     * @param \BetaKiller\Url\UrlContainerInterface $params
     * @param bool|null                             $useHidden
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     */
    private function processLayer(
        array $models,
        ?UrlContainerInterface $params = null,
        ?bool $useHidden = null
    ): \Generator {
        foreach ($models as $ifaceModel) {
            // Skip hidden ifaces
            if (!$useHidden && $ifaceModel->hideInSiteMap()) {
                continue;
            }

            foreach ($this->processSingle($ifaceModel, $params ?: new UrlContainer(), $useHidden) as $item) {
                yield $item;
            }
        }
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $ifaceModel
     * @param \BetaKiller\Url\UrlContainerInterface $params
     * @param bool|null                             $useHidden
     *
     * @return \Generator
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     *
     * @link https://github.com/MarkBaker/GeneratorQuadTrees/blob/master/src/PointQuadTree.php
     */
    private function processSingle(
        IFaceModelInterface $ifaceModel,
        UrlContainerInterface $params,
        ?bool $useHidden = null
    ): \Generator {
        $this->logger->debug('Processing :codename IFace', [':codename' => $ifaceModel->getCodename()]);

        $childs = $this->tree->getChildren($ifaceModel);

        $this->logger->debug('Total :num childs found for :codename IFace', [
            ':num'      => \count($childs),
            ':codename' => $ifaceModel->getCodename(),
        ]);

        $urlCounter = 0;

        foreach ($this->getAvailableIFaceUrls($ifaceModel, $params) as $availableUrl) {
            $urlParameter = $availableUrl->getUrlParameter();

            // Store parameter for childs processing
            if ($urlParameter) {
                $params->setParameter($urlParameter, true);
            }

            if (!$this->aclHelper->isIFaceAllowed($ifaceModel, $params)) {
                $this->logger->debug('Skip :codename IFace coz it is not allowed', [
                    ':codename' => $ifaceModel->getCodename(),
                ]);
                continue;
            }

            yield $availableUrl;
            $urlCounter++;

            // Recursion for childs
            foreach ($this->processLayer($childs, $params, $useHidden) as $childAvailableUrl) {
                yield $childAvailableUrl;
            }
        }

        $this->logger->debug('Total :num urls found', [':num' => $urlCounter]);
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
}
