<?php
declare(strict_types=1);

namespace BetaKiller\Helper;


use BetaKiller\Config\AppConfigInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceModelsStack;
use BetaKiller\IFace\IFaceModelTree;
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
     * @var \BetaKiller\IFace\IFaceModelsStack
     */
    private $stack;

    /**
     * @var \BetaKiller\IFace\IFaceModelTree
     */
    private $tree;

    /**
     * UrlHelper constructor.
     *
     * @param \BetaKiller\IFace\IFaceModelTree              $tree
     * @param \BetaKiller\Config\AppConfigInterface         $appConfig
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory $behaviourFactory
     * @param \BetaKiller\IFace\IFaceModelsStack            $stack
     */
    public function __construct(
        IFaceModelTree $tree,
        AppConfigInterface $appConfig,
        UrlBehaviourFactory $behaviourFactory,
        IFaceModelsStack $stack
    ) {
        $this->behaviourFactory = $behaviourFactory;
        $this->appConfig        = $appConfig;
        $this->stack            = $stack;
        $this->tree             = $tree;
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

        if ($removeCyclingLinks && $this->stack->isCurrent($model, $params)) {
            return $this->appConfig->getCircularLinkHref();
        }

        if ($model->isDefault()) {
            // Link to root for default IFace
            return $this->makeAbsoluteUrl('/');
        }

        $parts = [];

        foreach ($this->tree->getReverseBreadcrumbsIterator($model) as $item) {
            $uri = $this->makeIFaceUri($item, $params);
            array_unshift($parts, $uri);
        }

        $path = implode('/', array_filter($parts));

        if ($path && $this->appConfig->isTrailingSlashEnabled()) {
            // Add trailing slash before query parameters
            $split    = explode('?', $path, 2);
            $split[0] .= '/';
            $path     = implode('?', $split);
        }

        return $this->makeAbsoluteUrl($path);
    }

    public function makeAbsoluteUrl(string $relativeUrl): string
    {
        return $this->appConfig->getBaseUrl().$relativeUrl;
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
