<?php
namespace BetaKiller\Url;

use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use BetaKiller\Url\Container\UrlContainer;
use BetaKiller\Url\Container\UrlContainerInterface;

class AvailableUrlsCollector
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
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
     * @param \BetaKiller\Url\UrlElementTreeInterface       $tree
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory $behaviourFactory
     * @param \BetaKiller\Helper\AclHelper                  $aclHelper
     */
    public function __construct(
        UrlElementTreeInterface $tree,
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
     * @return \BetaKiller\Url\AvailableUri[]|\Generator
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getPublicAvailableUrls(?bool $useHidden = null): \Generator
    {
        $useHidden = $useHidden ?? false;

        $root = $this->tree->getRoot();

        // Use empty UrlContainer on each root IFace iteration (so no intersection of models between paths)
        foreach ($this->processLayer($root, null, $useHidden) as $item) {
            yield $item;
        }
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface[]           $models
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param bool|null                                       $useHidden
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function processLayer(
        array $models,
        ?UrlContainerInterface $params = null,
        ?bool $useHidden = null
    ): \Generator {
        foreach ($models as $urlElement) {
            // Skip hidden ifaces
            if (!$useHidden && $urlElement->hideInSiteMap()) {
                continue;
            }

            foreach ($this->processSingle($urlElement, $params ?: new UrlContainer(), $useHidden) as $item) {
                yield $item;
            }
        }
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param bool|null                                       $useHidden
     *
     * @return \Generator
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     *
     * @link https://github.com/MarkBaker/GeneratorQuadTrees/blob/master/src/PointQuadTree.php
     */
    private function processSingle(
        UrlElementInterface $urlElement,
        UrlContainerInterface $params,
        ?bool $useHidden = null
    ): \Generator {
        $this->logger->debug('Processing :codename IFace', [':codename' => $urlElement->getCodename()]);

        $childs = $this->tree->getChildren($urlElement);

        $this->logger->debug('Total :num childs found for :codename IFace', [
            ':num'      => \count($childs),
            ':codename' => $urlElement->getCodename(),
        ]);

        $urlCounter = 0;

        foreach ($this->getAvailableIFaceUrls($urlElement, $params) as $availableUrl) {
            $urlParameter = $availableUrl->getUrlParameter();

            // Store parameter for childs processing
            if ($urlParameter) {
                $params->setParameter($urlParameter, true);
            }

            try {
                if (!$this->aclHelper->isUrlElementAllowed($urlElement, $params)) {
                    $this->logger->debug('Skip :codename IFace coz it is not allowed', [
                        ':codename' => $urlElement->getCodename(),
                    ]);
                    continue;
                }
            } catch (\Spotman\Acl\Exception $e) {
                throw IFaceException::wrap($e);
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
     * @param \BetaKiller\Url\UrlElementInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function getAvailableIFaceUrls(UrlElementInterface $model, UrlContainerInterface $params): \Generator
    {
        $behaviour = $this->behaviourFactory->fromUrlElement($model);

        // TODO Deal with calculation of the last_modified from each parameter value

        foreach ($behaviour->getAvailableUrls($model, $params) as $availableUrl) {
            yield $availableUrl;
        }
    }
}
