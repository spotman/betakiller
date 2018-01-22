<?php
declare(strict_types=1);

namespace BetaKiller\Url\Behaviour;

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Url\UrlContainerInterface;
use BetaKiller\Url\UrlPathIterator;

interface UrlBehaviourInterface
{
    public const CLASS_NS     = ['Url', 'Behaviour'];
    public const CLASS_SUFFIX = 'UrlBehaviour';

    /**
     * Returns true if current behaviour was applied
     *
     * @param \BetaKiller\IFace\IFaceModelInterface      $model
     * @param \BetaKiller\Url\UrlPathIterator            $it
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     *
     * @throws \BetaKiller\Url\UrlBehaviourException
     *
     * @return bool
     */
    public function parseUri(
        IFaceModelInterface $model,
        UrlPathIterator $it,
        UrlContainerInterface $params
    ): bool;

    /**
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     */
//    public function getAvailableUris(): \Generator;
}
