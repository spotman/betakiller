<?php
declare(strict_types=1);

namespace BetaKiller\Url\Behaviour;

use BetaKiller\Helper\UrlHelper;
use BetaKiller\Url\AvailableUri;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\Parameter\UrlParameterInterface;
use BetaKiller\Url\UrlDispatcher;
use BetaKiller\Url\UrlElementInterface;

abstract class AbstractUrlBehaviour implements UrlBehaviourInterface
{
    /**
     * @var \BetaKiller\Helper\UrlHelper
     */
    protected $urlHelper;

    /**
     * AbstractUrlBehaviour constructor.
     *
     * @param \BetaKiller\Helper\UrlHelper $urlHelper
     */
    public function __construct(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    protected function createAvailableUri(string $uri, ?UrlParameterInterface $param = null): AvailableUri
    {
        return new AvailableUri($uri, $param);
    }

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
        UrlContainerInterface $params
    ): string {
        $uri = $this->getUri($urlElement, $params);

        // Link to the root if this is a default element
        if ($uri === UrlDispatcher::DEFAULT_URI && $urlElement->isDefault()) {
            $uri = '';
        }

        return $uri;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface                  $ifaceModel
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return string
     */
    abstract protected function getUri(UrlElementInterface $ifaceModel, UrlContainerInterface $params): string;
}
