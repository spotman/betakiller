<?php
declare(strict_types=1);

namespace BetaKiller\Url\Behaviour;

use BetaKiller\Factory\FactoryException;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Repository\RepositoryException;
use BetaKiller\Url\UrlBehaviourException;
use BetaKiller\Url\UrlContainerInterface;
use BetaKiller\Url\UrlDispatcher;
use BetaKiller\Url\UrlPathIterator;

class MultipleUrlBehaviour extends AbstractUrlBehaviour
{
    /**
     * @Inject
     * @var \BetaKiller\Url\UrlPrototypeHelper
     */
    private $urlPrototypeHelper;

    /**
     * Returns true if current behaviour was applied
     *
     * @param \BetaKiller\IFace\IFaceModelInterface      $model
     * @param \BetaKiller\Url\UrlPathIterator            $it
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     *
     * @return bool
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
     * @throws \BetaKiller\Url\UrlBehaviourException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    protected function parseUriParameterPart(
        IFaceModelInterface $ifaceModel,
        UrlPathIterator $it,
        UrlContainerInterface $urlContainer
    ): void {
        $uri = $ifaceModel->getUri();

        if (!$uri) {
            throw new UrlBehaviourException('IFace :codename must have uri', [
                ':codename' => $ifaceModel->getCodename(),
            ]);
        }

        $prototype = $this->urlPrototypeHelper->fromString($ifaceModel->getUri());

        // Root element have default uri
        $uriValue = ($it->rootRequested() || ($ifaceModel->isDefault() && !$ifaceModel->hasDynamicUrl()))
            ? UrlDispatcher::DEFAULT_URI
            : $it->current();

        try {
            $item = $this->urlPrototypeHelper->createParameterInstance($prototype, $uriValue);
        } catch (RepositoryException $e) {
            throw UrlBehaviourException::wrap($e);
        } catch (FactoryException $e) {
            throw UrlBehaviourException::wrap($e);
        }

        // Store model into registry
        // Allow tree url behaviour to set value multiple times
        $urlContainer->setParameter($item, $ifaceModel->hasTreeBehaviour());
    }
}
