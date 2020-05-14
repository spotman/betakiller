<?php
declare(strict_types=1);

namespace BetaKiller\Url\Behaviour;

use BetaKiller\Helper\UrlHelperInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlDispatcher;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlPathIterator;
use BetaKiller\Url\UrlPrototypeService;
use Generator;

class MultipleUrlBehaviour extends AbstractUrlBehaviour
{
    /**
     * @var \BetaKiller\Url\UrlPrototypeService
     */
    protected $prototypeService;

    /**
     * MultipleUrlBehaviour constructor.
     *
     * @param \BetaKiller\Helper\UrlHelperInterface $urlHelper
     * @param \BetaKiller\Url\UrlPrototypeService   $urlPrototypeService
     */
    public function __construct(UrlHelperInterface $urlHelper, UrlPrototypeService $urlPrototypeService)
    {
        $this->prototypeService = $urlPrototypeService;

        parent::__construct($urlHelper);
    }

    /**
     * Returns true if current behaviour was applied
     *
     * @param \BetaKiller\Url\UrlElementInterface                  $model
     * @param \BetaKiller\Url\UrlPathIterator                      $it
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return bool
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function parseUri(
        UrlElementInterface $model,
        UrlPathIterator $it,
        UrlContainerInterface $params
    ): bool {
        // Regular dynamic URL, parse uri
        $this->parseUriParameterPart($model, $it, $params);

        return true;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $urlElement
     * @param \BetaKiller\Url\UrlPathIterator                 $it
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlContainer
     *
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    protected function parseUriParameterPart(
        UrlElementInterface $urlElement,
        UrlPathIterator $it,
        UrlContainerInterface $urlContainer
    ): void {
        $prototype = $this->prototypeService->createPrototypeFromUrlElement($urlElement);

        // Root element have default uri
        $uriValue = ($it->rootRequested() || ($urlElement->isDefault() && !$urlElement->hasDynamicUrl()))
            ? UrlDispatcher::DEFAULT_URI
            : $it->current();

        $item = $this->prototypeService->createParameterInstance($prototype, $uriValue, $urlContainer);

        if (!$item) {
            throw new UrlBehaviourException('Can not find item for ":proto" by ":value"', [
                ':proto' => $prototype->asString(),
                ':value' => $uriValue,
            ]);
        }

        // Store model into registry
        // Allow tree url behaviour to set value multiple times
        $urlContainer->setParameter($item, $urlElement->hasTreeBehaviour());
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $ifaceModel
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    protected function getUri(
        UrlElementInterface $ifaceModel,
        UrlContainerInterface $params
    ): string {
        $proto = $this->prototypeService->createPrototypeFromUrlElement($ifaceModel);

        return $this->prototypeService->getCompiledPrototypeValue($proto, $params);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getAvailableUrls(
        UrlElementInterface $urlElement,
        UrlContainerInterface $params
    ): Generator {
        $prototype = $this->prototypeService->createPrototypeFromUrlElement($urlElement);
        $items     = $this->prototypeService->getAvailableParameters($prototype, $params);

        // Get clone of original filtering params so we`ll have all required params
        $ifaceUrlParams = clone $params;

        foreach ($items as $availableParameter) {
            $ifaceUrlParams->setParameter($availableParameter, true);
            $url = $this->urlHelper->makeUrl($urlElement, $ifaceUrlParams, false);

            yield $this->createAvailableUri($url, $availableParameter);

            if ($urlElement->hasTreeBehaviour()) {
                yield from $this->getAvailableUrls($urlElement, $ifaceUrlParams);
            }
        }
    }
}
