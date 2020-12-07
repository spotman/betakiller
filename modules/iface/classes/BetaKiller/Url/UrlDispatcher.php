<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Url\Behaviour\UrlBehaviourException;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use BetaKiller\Url\Container\UrlContainerInterface;

class UrlDispatcher implements UrlDispatcherInterface
{
    /**
     * Defines default uri for index element (this used if root IFace has dynamic url behaviour)
     */
    public const DEFAULT_URI = 'index';

    /**
     * @var \BetaKiller\Url\Behaviour\UrlBehaviourFactory
     */
    private $behaviourFactory;

    /**
     * @var \BetaKiller\Url\UrlPrototypeService
     */
    private $prototypeService;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @param \BetaKiller\Url\UrlElementTreeInterface       $tree
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory $behaviourFactory
     * @param \BetaKiller\Url\UrlPrototypeService           $prototypeService
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        UrlBehaviourFactory $behaviourFactory,
        UrlPrototypeService $prototypeService
    ) {
        $this->tree             = $tree;
        $this->behaviourFactory = $behaviourFactory;
        $this->prototypeService = $prototypeService;
    }

    /**
     * @param string                                          $uri
     *
     * @param \BetaKiller\Url\UrlElementStack                 $stack
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return void
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\MissingUrlElementException
     */
    public function process(string $uri, UrlElementStack $stack, UrlContainerInterface $params): void
    {
        // Prevent XSS via URL
        $uri = htmlspecialchars($uri, ENT_QUOTES);

        $path = parse_url($uri, PHP_URL_PATH);

        try {
            // Parse path first and detect target UrlElement
            $this->parseUriPath($path, $stack, $params);

            // Parse query parts for target UrlElement
            $this->parseQueryParts($stack->getCurrent(), $params);
        } catch (UrlBehaviourException $e) {
            // Fetch last processed parent (use root if not exists)
            $last = $stack->hasCurrent() ? $stack->getCurrent() : null;

            throw new MissingUrlElementException($params, $last, false, $e);
        }
    }

    /**
     * Performs parsing of requested url query parts
     *
     * @param \BetaKiller\Url\UrlElementInterface             $element
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    private function parseQueryParts(UrlElementInterface $element, UrlContainerInterface $params): void
    {
        foreach ($element->getQueryParams() as $key => $binding) {
            $this->processQueryPart($key, $binding, $params);
        }
    }

    /**
     * @param string                                          $key
     * @param string                                          $binding
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParams
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    private function processQueryPart(string $key, string $binding, UrlContainerInterface $urlParams): void
    {
        $partValue = $urlParams->getQueryPart($key);

        // Skip missing parts
        if (!$partValue) {
            return;
        }

        $prototype = $this->prototypeService->createPrototypeFromString(sprintf('{%s}', $binding));

        if (\is_array($partValue)) {
            // TODO Deal with this case (UrlContainer can not handler multiple instances, only the last one is stored)
            foreach ($partValue as $item) {
                $this->processQueryPartValue($prototype, $item, $urlParams);
            }
        }

        if (\is_string($partValue)) {
            $this->processQueryPartValue($prototype, $partValue, $urlParams);
        }
    }

    private function processQueryPartValue(UrlPrototype $prototype, string $partValue, UrlContainerInterface $urlParams): void
    {
        $item = $this->prototypeService->createParameterInstance($prototype, $partValue, $urlParams);

        if (!$item) {
            throw new UrlBehaviourException('Can not find item for ":proto" by ":value"', [
                ':proto' => $prototype->asString(),
                ':value' => $partValue,
            ]);
        }

        // Store parameter in registry
        $urlParams->setParameter($item, false);
    }

    /**
     * Performs parsing of requested url path
     *
     * @param string                                          $uri
     * @param \BetaKiller\Url\UrlElementStack                 $stack
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParams
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     * @throws \BetaKiller\Url\MissingUrlElementException
     */
    private function parseUriPath(string $uri, UrlElementStack $stack, UrlContainerInterface $urlParams): void
    {
        // Creating URL iterator
        $urlIterator = new UrlPathIterator($uri);

        $parent = null;

        // Dispatch childs
        // Loop through every uri part and initialize it`s iface
        do {
            $urlElement = $this->detectUrlElement($urlIterator, $urlParams, $parent);

            $parent = $urlElement;

            $stack->push($urlElement);

            $urlIterator->next();
        } while ($urlIterator->valid());
    }

    /**
     * @param \BetaKiller\Url\UrlPathIterator                 $it
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Url\UrlElementInterface|null        $parent
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\Factory\FactoryException
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
            throw new MissingUrlElementException($params, $parent, true);
        }

        // Search for appropriate model in current layer
        $urlElement = $this->selectUrlElementModel($layer, $it, $params);

        if (!$urlElement) {
            // No UrlElement found
            throw new MissingUrlElementException($params, $parent);
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
