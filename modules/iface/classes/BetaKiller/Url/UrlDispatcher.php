<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Url\Behaviour\UrlBehaviourException;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use BetaKiller\Url\Container\UrlContainerInterface;

class UrlDispatcher implements UrlDispatcherInterface
{
    use LoggerHelperTrait;

    /**
     * Defines default uri for index element (this used if root IFace has dynamic url behaviour)
     */
    public const DEFAULT_URI = 'index';

    /**
     * @var \BetaKiller\Url\Behaviour\UrlBehaviourFactory
     */
    private $behaviourFactory;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @param \BetaKiller\Url\UrlElementTreeInterface       $tree
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory $behaviourFactory
     */
    public function __construct(UrlElementTreeInterface $tree, UrlBehaviourFactory $behaviourFactory)
    {
        $this->tree             = $tree;
        $this->behaviourFactory = $behaviourFactory;
    }

    /**
     * @param string                                          $uri
     *
     * @param \BetaKiller\Url\UrlElementStack                 $stack
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return void
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\MissingUrlElementException
     */
    public function process(string $uri, UrlElementStack $stack, UrlContainerInterface $params): void
    {
        // Prevent XSS via URL
        $uri = htmlspecialchars($uri, ENT_QUOTES);

        $path = parse_url($uri, PHP_URL_PATH);

        $this->parseUriPath($path, $stack, $params);
    }

    /**
     * Performs parsing of requested url
     * Returns IFace
     *
     * @param string                                          $uri
     *
     * @param \BetaKiller\Url\UrlElementStack                 $stack
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParams
     *
     * @return void
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\MissingUrlElementException
     */
    private function parseUriPath(string $uri, UrlElementStack $stack, UrlContainerInterface $urlParams): void
    {
        // Creating URL iterator
        $urlIterator = new UrlPathIterator($uri);

        $parent = null;

        try {
            // Dispatch childs
            // Loop through every uri part and initialize it`s iface
            do {
                $urlElement = $this->detectUrlElement($urlIterator, $urlParams, $parent);

                $parent = $urlElement;

                $stack->push($urlElement);

                $urlIterator->next();
            } while ($urlIterator->valid());
        } catch (UrlBehaviourException $e) {
            throw new MissingUrlElementException($parent, false, $e);
        }
    }

    /**
     * @param \BetaKiller\Url\UrlPathIterator                 $it
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Url\UrlElementInterface|null        $parent
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     * @throws \BetaKiller\Url\MissingUrlElementException
     */
    private function detectUrlElement(
        UrlPathIterator $it,
        UrlContainerInterface $params,
        ?UrlElementInterface $parent
    ): UrlElementInterface {
        if ($it->rootRequested()) {
            $defaultModel = $this->tree->getDefault();

            $this->processUrlBehaviour($defaultModel, $it, $params);

            return $defaultModel;
        }

        // Get child IFaces
        $layer = $parent
            ? $this->tree->getChildren($parent)
            : $this->tree->getRoot();

        // Empty layer (bad copy-paste, mistake, etc)
        if (!$layer) {
            // Force redirect to parent URL
            throw new MissingUrlElementException($parent, true);
        }

        // Search for appropriate model in current layer
        $urlElement = $this->selectUrlElementModel($layer, $it, $params);

        if (!$urlElement) {
            // No UrlElement found
            throw new MissingUrlElementException($parent);
        }

        return $urlElement;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface[] $models
     *
     * @return \BetaKiller\Url\UrlElementInterface[]
     */
    private function sortModelsLayer(array $models): array
    {
        $fixed   = [];
        $dynamic = [];

        foreach ($models as $model) {
            if ($model->hasDynamicUrl() || $model->hasTreeBehaviour()) {
                $dynamic[] = $model;
            } else {
                $fixed[] = $model;
            }
        }

        // Fixed URLs first, dynamic URLs last
        return array_merge($fixed, $dynamic);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface[]           $models
     * @param UrlPathIterator                                 $it
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParameters
     *
     * @return \BetaKiller\Url\UrlElementInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     */
    private function selectUrlElementModel(
        array $models,
        UrlPathIterator $it,
        UrlContainerInterface $urlParameters
    ): ?UrlElementInterface {
        // Put fixed urls first
        $models = $this->sortModelsLayer($models);

        foreach ($models as $model) {
            if ($this->processUrlBehaviour($model, $it, $urlParameters)) {
                return $model;
            }
        }

        // Nothing found
        return null;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $model
     * @param \BetaKiller\Url\UrlPathIterator                 $it
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParameters
     *
     * @return bool
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     */
    private function processUrlBehaviour(
        UrlElementInterface $model,
        UrlPathIterator $it,
        UrlContainerInterface $urlParameters
    ): bool {
        return $this->behaviourFactory->fromUrlElement($model)->parseUri($model, $it, $urlParameters);
    }
}
