<?php
declare(strict_types=1);

namespace BetaKiller\Url\Behaviour;

use BetaKiller\Factory\FactoryException;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Repository\RepositoryException;
use BetaKiller\Url\AvailableUri;
use BetaKiller\Url\UrlBehaviourException;
use BetaKiller\Url\UrlContainerInterface;
use BetaKiller\Url\UrlDispatcher;
use BetaKiller\Url\UrlPathIterator;

class MultipleUrlBehaviour extends AbstractUrlBehaviour
{
    /**
     * @var \BetaKiller\Url\UrlPrototypeService
     * @Inject
     */
    private $urlPrototypeService;

    /**
     * @var \BetaKiller\Helper\UrlHelper
     * @Inject
     */
    private $urlHelper;

    /**
     * Returns true if current behaviour was applied
     *
     * @param \BetaKiller\IFace\IFaceModelInterface      $model
     * @param \BetaKiller\Url\UrlPathIterator            $it
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     *
     * @return bool
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \BetaKiller\Url\UrlBehaviourException
     */
    public function parseUri(
        IFaceModelInterface $model,
        UrlPathIterator $it,
        UrlContainerInterface $params
    ): bool {
        // Regular dynamic URL, parse uri
        $this->parseUriParameterPart($model, $it, $params);

        return true;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $ifaceModel
     * @param \BetaKiller\Url\UrlPathIterator       $it
     *
     * @param \BetaKiller\Url\UrlContainerInterface $urlContainer
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\Url\UrlBehaviourException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    protected function parseUriParameterPart(
        IFaceModelInterface $ifaceModel,
        UrlPathIterator $it,
        UrlContainerInterface $urlContainer
    ): void {
        $prototype = $this->urlPrototypeService->createPrototypeFromIFaceModel($ifaceModel);

        // Root element have default uri
        $uriValue = ($it->rootRequested() || ($ifaceModel->isDefault() && !$ifaceModel->hasDynamicUrl()))
            ? UrlDispatcher::DEFAULT_URI
            : $it->current();

        try {
            $item = $this->urlPrototypeService->createParameterInstance($prototype, $uriValue);
        } catch (RepositoryException $e) {
            throw UrlBehaviourException::wrap($e);
        } catch (FactoryException $e) {
            throw UrlBehaviourException::wrap($e);
        }

        // Store model into registry
        // Allow tree url behaviour to set value multiple times
        $urlContainer->setParameter($item, $ifaceModel->hasTreeBehaviour());
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $ifaceModel
     * @param \BetaKiller\Url\UrlContainerInterface $params
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    protected function getUri(
        IFaceModelInterface $ifaceModel,
        ?UrlContainerInterface $params = null
    ): string {
        return $this->urlPrototypeService->getCompiledPrototypeValue($ifaceModel->getUri(), $params,
            $ifaceModel->hasTreeBehaviour());
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $ifaceModel
     * @param \BetaKiller\Url\UrlContainerInterface $params
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getAvailableUrls(IFaceModelInterface $ifaceModel, UrlContainerInterface $params): \Generator
    {
        $prototype = $this->urlPrototypeService->createPrototypeFromIFaceModel($ifaceModel);
        $items     = $this->urlPrototypeService->getAvailableParameters($prototype, $params);

        // Get clone of original filtering params so we`ll have all required params
        $ifaceUrlParams = clone $params;

        foreach ($items as $availableParameter) {
            $ifaceUrlParams->setParameter($availableParameter);
            $url = $this->urlHelper->makeIFaceUrl($ifaceModel, $ifaceUrlParams);

            yield new AvailableUri($url, $availableParameter);

            if ($ifaceModel->hasTreeBehaviour()) {
                foreach ($this->getAvailableUrls($ifaceModel, $ifaceUrlParams) as $treeAvailableUrl) {
                    yield $treeAvailableUrl;
                }
            }
        }
    }
}
