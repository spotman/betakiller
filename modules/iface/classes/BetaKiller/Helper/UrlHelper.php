<?php
declare(strict_types=1);

namespace BetaKiller\Helper;


use BetaKiller\Config\AppConfigInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceStack;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use BetaKiller\Url\UrlContainerInterface;

class UrlHelper
{
    /**
     * @var \BetaKiller\Url\Behaviour\UrlBehaviourFactory
     */
    private $behaviourFactory;

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * @var \BetaKiller\IFace\IFaceStack
     */
    private $stack;

    /**
     * UrlHelper constructor.
     *
     * @param \BetaKiller\Config\AppConfigInterface         $appConfig
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory $behaviourFactory
     * @param \BetaKiller\IFace\IFaceStack                  $stack
     */
    public function __construct(AppConfigInterface $appConfig, UrlBehaviourFactory $behaviourFactory, IFaceStack $stack)
    {
        $this->behaviourFactory = $behaviourFactory;
        $this->appConfig        = $appConfig;
        $this->stack            = $stack;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface      $model
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     * @param bool|null                                  $removeCyclingLinks
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function makeIFaceUrl(
        IFaceModelInterface $model,
        ?UrlContainerInterface $params = null,
        ?bool $removeCyclingLinks = null
    ): string {
        $removeCyclingLinks = $removeCyclingLinks ?? true;

        if ($removeCyclingLinks && $this->stack->isCurrentModel($model, $params)) {
            return $this->appConfig->getCircularLinkHref();
        }

        $parts   = [];
        $current = $model;
        $parent  = null;

        // TODO Replace with IFaceTree traversing (climbing)
        do {
            $uri = $this->makeIFaceUri($current, $params);
            array_unshift($parts, $uri);

            $parent  = $current->getParent();
            $current = $parent;
        } while ($parent);

        $path = implode('/', $parts);

        if ($this->appConfig->isTrailingSlashEnabled()) {
            // Add trailing slash before query parameters
            $split    = explode('?', $path, 2);
            $split[0] .= '/';
            $path     = implode('?', $split);
        }

        return $this->appConfig->getBaseUrl().$path;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     * @param \BetaKiller\Url\UrlContainerInterface $urlContainer
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function makeIFaceUri(IFaceModelInterface $model, UrlContainerInterface $urlContainer = null): string
    {
        $uri = $model->getUri();

        if (!$uri) {
            throw new IFaceException('IFace :codename must have uri', [':codename' => $model->getCodename()]);
        }

        $behaviour = $this->behaviourFactory->fromIFaceModel($model);

        return $behaviour->makeUri($model, $urlContainer);
    }
}
