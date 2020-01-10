<?php
namespace BetaKiller\Url;

use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use BetaKiller\Url\Container\ResolvingUrlContainer;
use BetaKiller\Url\Container\UrlContainerInterface;
use Psr\Log\LoggerInterface;

class AvailableUrlsCollector
{
    use LoggerHelperTrait;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
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
        AclHelper $aclHelper,
        LoggerInterface $logger
    ) {
        $this->tree             = $tree;
        $this->aclHelper        = $aclHelper;
        $this->behaviourFactory = $behaviourFactory;
        $this->logger           = $logger;
    }

    /**
     * @param bool|null                    $useHidden
     *
     * @return \BetaKiller\Url\AvailableUri[]|\Generator
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getPublicAvailableUrls(?bool $useHidden = null): \Generator
    {
        $useHidden = $useHidden ?? false;

        $root = $this->tree->getRoot();

        yield from $this->processLayer($root, null, $useHidden);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface[]           $models
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param bool|null                                       $useHidden
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlElementException
     */
    private function processLayer(
        array $models,
        ?UrlContainerInterface $params = null,
        ?bool $useHidden = null
    ): \Generator {
        foreach ($models as $urlElement) {
            // Skip hidden ifaces
            if (!$useHidden && $urlElement->isHiddenInSiteMap()) {
                $this->logger->debug('Skip hidden URL element ":name"', [':name' => $urlElement->getCodename()]);
                continue;
            }

            yield from $this->processSingle(
                $urlElement,
                // Use empty UrlContainer on each root element iteration (so no intersection of models between paths)
                $params ?: ResolvingUrlContainer::create(),
                $useHidden
            );
        }
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @param bool|null                                       $useHidden
     *
     * @return \Generator
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlElementException
     * @link https://github.com/MarkBaker/GeneratorQuadTrees/blob/master/src/PointQuadTree.php
     */
    private function processSingle(
        UrlElementInterface $urlElement,
        UrlContainerInterface $params,
        ?bool $useHidden = null
    ): \Generator {
        $this->logger->debug('Processing ":codename" URL element', [':codename' => $urlElement->getCodename()]);

        $childs = $this->tree->getChildren($urlElement);

        $this->logger->debug('Total :num childs found for ":codename" URL element', [
            ':num'      => \count($childs),
            ':codename' => $urlElement->getCodename(),
        ]);

        $urlCounter = 0;
        $guest      = $this->aclHelper->getGuestUser();

        foreach ($this->getAvailableIFaceUrls($urlElement, $params) as $availableUrl) {
            $urlParameter = $availableUrl->getUrlParameter();

            // Store parameter for childs processing
            if ($urlParameter) {
                $params->setParameter($urlParameter, true);
            }

            try {
                if (!$this->aclHelper->isUrlElementAllowed($guest, $urlElement, $params)) {
                    $this->logger->debug('Skip ":codename" URL element coz it is not allowed', [
                        ':codename' => $urlElement->getCodename(),
                    ]);
                    continue;
                }
            } catch (\Spotman\Acl\AclException $e) {
                throw UrlElementException::wrap($e);
            }

            yield $availableUrl;
            $urlCounter++;

            // Recursion for childs
            foreach ($this->processLayer($childs, $params, $useHidden) as $childAvailableUrl) {
                yield $childAvailableUrl;
                $urlCounter++;
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
    private function getAvailableIFaceUrls(
        UrlElementInterface $model,
        UrlContainerInterface $params
    ): \Generator {
        $behaviour = $this->behaviourFactory->fromUrlElement($model);

        // TODO Deal with calculation of the last_modified from each parameter value

        yield from $behaviour->getAvailableUrls($model, $params);
    }
}
