<?php
declare(strict_types=1);

namespace BetaKiller\Url\Behaviour;

use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlPathIterator;
use Generator;

class SingleUrlBehaviour extends AbstractUrlBehaviour
{
    /**
     * Returns true if current behaviour was applied
     *
     * @param \BetaKiller\Url\UrlElementInterface                  $urlElement
     * @param \BetaKiller\Url\UrlPathIterator                      $it
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return bool
     */
    public function parseUri(
        UrlElementInterface $urlElement,
        UrlPathIterator $it,
        UrlContainerInterface $params
    ): bool {
        // Return true if fixed url found
        return $urlElement->getUri() === $it->current();
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface                  $ifaceModel
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return string
     */
    protected function getUri(
        UrlElementInterface $ifaceModel,
        UrlContainerInterface $params
    ): string {
        return $ifaceModel->getUri();
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface                  $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getAvailableUrls(
        UrlElementInterface $urlElement,
        UrlContainerInterface $params
    ): Generator {
        $url = $this->urlHelper->makeUrl($urlElement, $params, false);

        // Only one available uri and no UrlParameter instance
        yield $this->createAvailableUri($url);
    }
}
