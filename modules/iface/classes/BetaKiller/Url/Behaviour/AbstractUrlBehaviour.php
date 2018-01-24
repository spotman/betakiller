<?php
declare(strict_types=1);

namespace BetaKiller\Url\Behaviour;

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Url\AvailableUri;
use BetaKiller\Url\UrlContainerInterface;
use BetaKiller\Url\UrlDispatcher;
use BetaKiller\Url\UrlParameterInterface;

abstract class AbstractUrlBehaviour implements UrlBehaviourInterface
{
    protected function createAvailableUri(string $uri, ?UrlParameterInterface $param = null): AvailableUri
    {
        return new AvailableUri($uri, $param);
    }

    /**
     * Returns IFace uri part based on an optional UrlContainer
     *
     * @param \BetaKiller\IFace\IFaceModelInterface      $ifaceModel
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     *
     * @return string
     */
    public function makeUri(
        IFaceModelInterface $ifaceModel,
        ?UrlContainerInterface $params = null
    ): string {
        $uri = $this->getUri($ifaceModel, $params);

        // Link to the root if this is a default element
        if ($uri === UrlDispatcher::DEFAULT_URI && $ifaceModel->isDefault()) {
            $uri = '';
        }

        return $uri;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface      $ifaceModel
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     *
     * @return string
     */
    abstract protected function getUri(
        IFaceModelInterface $ifaceModel,
        ?UrlContainerInterface $params = null
    ): string;


}
