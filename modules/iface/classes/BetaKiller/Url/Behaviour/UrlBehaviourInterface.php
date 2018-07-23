<?php
declare(strict_types=1);

namespace BetaKiller\Url\Behaviour;

use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlPathIterator;

interface UrlBehaviourInterface
{
    public const CLASS_NS     = ['Url', 'Behaviour'];
    public const CLASS_SUFFIX = 'UrlBehaviour';

    /**
     * Returns true if current behaviour was applied
     *
     * @param \BetaKiller\Url\UrlElementInterface                  $urlElement
     * @param \BetaKiller\Url\UrlPathIterator                      $it
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     *
     * @return bool
     */
    public function parseUri(
        UrlElementInterface $urlElement,
        UrlPathIterator $it,
        UrlContainerInterface $params
    ): bool;

    /**
     * Returns IFace uri part based on an optional UrlContainer
     *
     * @param \BetaKiller\Url\UrlElementInterface                  $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return string
     */
    public function makeUri(
        UrlElementInterface $urlElement,
        ?UrlContainerInterface $params = null
    ): string;

    /**
     * @param \BetaKiller\Url\UrlElementInterface                  $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     */
    public function getAvailableUrls(
        UrlElementInterface $urlElement,
        UrlContainerInterface $params
    ): \Generator;
}
