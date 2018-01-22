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
}
