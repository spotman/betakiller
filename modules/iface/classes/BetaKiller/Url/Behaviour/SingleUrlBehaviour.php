<?php
declare(strict_types=1);

namespace BetaKiller\Url\Behaviour;

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Url\UrlContainerInterface;
use BetaKiller\Url\UrlPathIterator;

class SingleUrlBehaviour extends AbstractUrlBehaviour
{
    /**
     * Returns true if current behaviour was applied
     *
     * @param \BetaKiller\IFace\IFaceModelInterface      $model
     * @param \BetaKiller\Url\UrlPathIterator            $it
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     *
     * @return bool
     */
    public function parseUri(
        IFaceModelInterface $model,
        UrlPathIterator $it,
        UrlContainerInterface $params
    ): bool {
        // Return true if fixed url found
        return $model->getUri() === $it->current();
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface      $ifaceModel
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     *
     * @return string
     */
    protected function getUri(
        IFaceModelInterface $ifaceModel,
        ?UrlContainerInterface $params = null
    ): string {
        return $ifaceModel->getUri();
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface      $ifaceModel
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     */
    public function getAvailableUrls(
        IFaceModelInterface $ifaceModel,
        UrlContainerInterface $params
    ): \Generator {
        // Only one available uri and no UrlParameter instance
        yield $this->createAvailableUri(
            $this->makeUri($ifaceModel, $params)
        );
    }
}
