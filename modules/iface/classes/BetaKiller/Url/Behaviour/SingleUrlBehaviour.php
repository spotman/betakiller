<?php
declare(strict_types=1);

namespace BetaKiller\Url\Behaviour;

use BetaKiller\Url\UrlContainerInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlPathIterator;

class SingleUrlBehaviour extends AbstractUrlBehaviour
{
    /**
     * @Inject
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * Returns true if current behaviour was applied
     *
     * @param \BetaKiller\Url\UrlElementInterface $urlElement
     * @param \BetaKiller\Url\UrlPathIterator               $it
     * @param \BetaKiller\Url\UrlContainerInterface|null    $params
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
     * @param \BetaKiller\Url\UrlElementInterface        $urlElement
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     *
     * @return string
     */
    protected function getUri(
        UrlElementInterface $urlElement,
        ?UrlContainerInterface $params = null
    ): string {
        return $urlElement->getUri();
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface        $urlElement
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     *
     * @return \Generator|\BetaKiller\Url\AvailableUri[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getAvailableUrls(
        UrlElementInterface $urlElement,
        UrlContainerInterface $params
    ): \Generator {
        $url = $this->urlHelper->makeUrl($urlElement, $params);

        // Only one available uri and no UrlParameter instance
        yield $this->createAvailableUri($url);
    }
}
