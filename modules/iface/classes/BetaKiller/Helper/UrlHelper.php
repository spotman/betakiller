<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Factory\FactoryException;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use BetaKiller\Url\UrlContainerInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementStack;
use BetaKiller\Url\UrlElementTreeInterface;

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
     * @var \BetaKiller\Url\UrlElementStack
     */
    private $stack;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * UrlHelper constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface       $tree
     * @param \BetaKiller\Config\AppConfigInterface         $appConfig
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory $behaviourFactory
     * @param \BetaKiller\Url\UrlElementStack               $stack
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        AppConfigInterface $appConfig,
        UrlBehaviourFactory $behaviourFactory,
        UrlElementStack $stack
    ) {
        $this->behaviourFactory = $behaviourFactory;
        $this->appConfig        = $appConfig;
        $this->stack            = $stack;
        $this->tree             = $tree;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface        $model
     * @param \BetaKiller\Url\UrlContainerInterface|null $params
     * @param bool|null                                  $removeCyclingLinks
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function makeUrl(
        UrlElementInterface $model,
        ?UrlContainerInterface $params = null,
        ?bool $removeCyclingLinks = null
    ): string {
        $removeCyclingLinks = $removeCyclingLinks ?? true;

        if ($removeCyclingLinks && $this->stack->isCurrent($model, $params)) {
            return $this->appConfig->getCircularLinkHref();
        }

        $parts = [];

        foreach ($this->tree->getReverseBreadcrumbsIterator($model) as $item) {
            $uri = $this->makeUrlElementUri($item, $params);
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
     * @param \BetaKiller\Url\UrlElementInterface   $model
     * @param \BetaKiller\Url\UrlContainerInterface $urlContainer
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function makeUrlElementUri(UrlElementInterface $model, UrlContainerInterface $urlContainer = null): string
    {
        $uri = $model->getUri();

        if (!$uri) {
            throw new IFaceException('IFace :codename must have uri', [':codename' => $model->getCodename()]);
        }

        try {
            $behaviour = $this->behaviourFactory->fromUrlElement($model);
        } catch (FactoryException $e) {
            throw IFaceException::wrap($e);
        }

        return $behaviour->makeUri($model, $urlContainer);
    }
}
