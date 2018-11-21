<?php
declare(strict_types=1);

namespace BetaKiller\Url\Behaviour;

use BetaKiller\Factory\FactoryException;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\Repository\RepositoryException;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlDispatcher;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlPathIterator;
use BetaKiller\Url\UrlPrototypeService;

class MultipleUrlBehaviour extends AbstractUrlBehaviour
{
    /**
     * @var \BetaKiller\Url\UrlPrototypeService
     */
    protected $prototypeService;

    /**
     * MultipleUrlBehaviour constructor.
     *
     * @param \BetaKiller\Url\UrlPrototypeService $urlPrototypeService
     */
    public function __construct(UrlPrototypeService $urlPrototypeService)
    {
        $this->prototypeService = $urlPrototypeService;
    }

    /**
     * Returns true if current behaviour was applied
     *
     * @param \BetaKiller\Url\UrlElementInterface                  $model
     * @param \BetaKiller\Url\UrlPathIterator                      $it
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return bool
     * @throws \BetaKiller\IFace\Exception\UrlElementException
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
     * @throws \BetaKiller\IFace\Exception\UrlElementException
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

        try {
            $item = $this->prototypeService->createParameterInstance($prototype, $uriValue, $urlContainer);
        } catch (RepositoryException $e) {
            throw UrlBehaviourException::wrap($e);
        } catch (FactoryException $e) {
            throw UrlBehaviourException::wrap($e);
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
        return $this->prototypeService->getCompiledPrototypeValue($ifaceModel->getUri(), $params);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Helper\UrlHelper                    $urlHelper
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getAvailableUrls(
        UrlElementInterface $urlElement,
        UrlContainerInterface $params,
        UrlHelper $urlHelper
    ): \Generator {
        $prototype = $this->prototypeService->createPrototypeFromUrlElement($urlElement);
        $items     = $this->prototypeService->getAvailableParameters($prototype, $params);

        // Get clone of original filtering params so we`ll have all required params
        $ifaceUrlParams = clone $params;

        foreach ($items as $availableParameter) {
            $ifaceUrlParams->setParameter($availableParameter, true);
            $url = $urlHelper->makeUrl($urlElement, $ifaceUrlParams, false);

            yield $this->createAvailableUri($url, $availableParameter);

            if ($urlElement->hasTreeBehaviour()) {
                yield from $this->getAvailableUrls($urlElement, $ifaceUrlParams, $urlHelper);
            }
        }
    }
}
