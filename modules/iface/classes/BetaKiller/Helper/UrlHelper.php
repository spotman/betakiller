<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\CrudlsActionsInterface;
use BetaKiller\Factory\FactoryException;
use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use BetaKiller\Url\Container\ResolvingUrlContainer;
use BetaKiller\Url\Container\UrlContainer;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\UrlDispatcher;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementStack;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\ZoneInterface;

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
     * @param string $codename
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getUrlElementByCodename(string $codename): UrlElementInterface
    {
        return $this->tree->getByCodename($codename);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface                  $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     *
     * @return bool
     */
    public function inStack(UrlElementInterface $model, UrlContainerInterface $params = null): bool
    {
        return $this->stack->has($model, $params);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface                  $urlElement
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     * @param bool|null                                            $removeCyclingLinks
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function makeUrl(
        UrlElementInterface $urlElement,
        ?UrlContainerInterface $params = null,
        ?bool $removeCyclingLinks = null
    ): string {
        $removeCyclingLinks = $removeCyclingLinks ?? true;

        if ($removeCyclingLinks && $this->stack->isCurrent($urlElement, $params)) {
            return $this->appConfig->getCircularLinkHref();
        }

        $parts = [];

        foreach ($this->tree->getReverseBreadcrumbsIterator($urlElement) as $item) {
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

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $action
     * @param string                                        $zone
     *
     * @param bool|null                                     $removeCycling
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getEntityUrl(
        DispatchableEntityInterface $entity,
        string $action,
        string $zone,
        ?bool $removeCycling = null
    ): string {
        $params = ResolvingUrlContainer::create();
        $params->setParameter($entity);

        // Search for URL element with provided entity, action and zone
        $urlElement = $this->tree->getByEntityActionAndZone($entity, $action, $zone);

        return $this->makeUrl($urlElement, $params, $removeCycling);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $zone
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getCreateEntityUrl(DispatchableEntityInterface $entity, string $zone): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_CREATE, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $zone
     *
     * @param bool|null                                     $removeCycling
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getReadEntityUrl(
        DispatchableEntityInterface $entity,
        string $zone,
        ?bool $removeCycling = null
    ): string {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_READ, $zone, $removeCycling);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $zone
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getUpdateEntityUrl(DispatchableEntityInterface $entity, string $zone): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_UPDATE, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $zone
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getDeleteEntityUrl(DispatchableEntityInterface $entity, string $zone): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_DELETE, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $zone
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getListEntityUrl(DispatchableEntityInterface $entity, string $zone): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_LIST, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $zone
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getSearchEntityUrl(DispatchableEntityInterface $entity, string $zone): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_SEARCH, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     *
     * @return null|string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getPreviewEntityUrl(DispatchableEntityInterface $entity): ?string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_READ, ZoneInterface::PREVIEW);
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    public function isValidUrl(string $url): bool
    {
        $dispatcher = new UrlDispatcher($this->tree, $this->behaviourFactory);
        $params     = new UrlContainer();
        $stack      = new UrlElementStack($params);

        try {
            $path = parse_url($url, PHP_URL_PATH);
            $dispatcher->process($path, $stack, $params);

            return true;
        } /** @noinspection BadExceptionsProcessingInspection */ catch (\Throwable $e) {
            // No logging in this case
            return false;
        }
    }

    private function makeAbsoluteUrl(string $relativeUrl): string
    {
        return $this->appConfig->getBaseUrl().$relativeUrl;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlContainer
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function makeUrlElementUri(UrlElementInterface $model, UrlContainerInterface $urlContainer = null): string
    {
        $uri = $model->getUri();

        if (!$uri) {
            throw new UrlElementException('UrlElement :codename must have uri', [':codename' => $model->getCodename()]);
        }

        try {
            $behaviour = $this->behaviourFactory->fromUrlElement($model);
        } catch (FactoryException $e) {
            throw UrlElementException::wrap($e);
        }

        return $behaviour->makeUri($model, $urlContainer);
    }
}
