<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\CrudlsActionsInterface;
use BetaKiller\Factory\FactoryException;
use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Url\Behaviour\UrlBehaviourException;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use BetaKiller\Url\Container\ResolvingUrlContainer;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\DummyModelInterface;
use BetaKiller\Url\IFaceModelInterface;
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
     * @var \BetaKiller\Url\Container\UrlContainerInterface
     */
    private $urlContainer;

    /**
     * UrlHelper constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface         $tree
     * @param \BetaKiller\Config\AppConfigInterface           $appConfig
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory   $behaviourFactory
     * @param \BetaKiller\Url\UrlElementStack                 $stack
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        AppConfigInterface $appConfig,
        UrlBehaviourFactory $behaviourFactory,
        UrlElementStack $stack,
        UrlContainerInterface $params
    ) {
        $this->behaviourFactory = $behaviourFactory;
        $this->appConfig        = $appConfig;
        $this->stack            = $stack;
        $this->tree             = $tree;
        $this->urlContainer     = $params;
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
     * @return \BetaKiller\Url\UrlElementInterface
     */
    public function getDefaultUrlElement(): UrlElementInterface
    {
        return $this->tree->getDefault();
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
     * @return \BetaKiller\Url\UrlElementInterface
     */
    public function getCurrentElement(): UrlElementInterface
    {
        return $this->stack->getCurrent();
    }

    /**
     * Returns current UrlElementStack
     *
     * @return \BetaKiller\Url\UrlElementStack
     */
    public function getStack(): UrlElementStack
    {
        return $this->stack;
    }

    public function createUrlContainer(): UrlContainerInterface
    {
        return ResolvingUrlContainer::create();
    }

    /**
     * Returns current UrlContainer
     *
     * @return \BetaKiller\Url\Container\UrlContainerInterface
     */
    public function getUrlContainer(): UrlContainerInterface
    {
        return $this->urlContainer;
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

        // Make link to Dummy redirect target (prevent browser redirects)
        if ($urlElement instanceof DummyModelInterface) {
            $urlElement = $this->detectDummyTarget($urlElement);
        }

        if ($removeCyclingLinks && $this->stack->isCurrent($urlElement, $params)) {
            return $this->appConfig->getCircularLinkHref();
        }

        if ($urlElement->isDefault() && !$urlElement->hasDynamicUrl()) {
            return $this->makeAbsoluteUrl('/');
        }

        // Use self-resolving container as default
        $params = $params ?: $this->createUrlContainer();

        // Import current UrlContainer values for simplicity in the client code
        $params->import($this->urlContainer);

        $parts = [];

        foreach ($this->tree->getBranchIterator($urlElement) as $item) {
            $parts[] = $this->makeUrlElementUri($item, $params);
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
        $params = $this->createUrlContainer();
        $params->setParameter($entity);

        // Search for URL element with provided entity, action and zone
        $urlElement = $this->tree->getByEntityActionAndZone($entity::getModelName(), $action, $zone);

        return $this->makeUrl($urlElement, $params, $removeCycling);
    }

    public function getEntityNameUrl(
        string $entityName,
        string $action,
        string $zone,
        ?bool $removeCycling = null
    ): string {
        if (!\in_array($action, CrudlsActionsInterface::ACTIONS_WITHOUT_ENTITY, true)) {
            throw new UrlBehaviourException('Action ":action" requires entity instance for URL generation', [
                ':action' => $action,
            ]);
        }

        // Search for URL element with provided entity, action and zone
        $urlElement = $this->tree->getByEntityActionAndZone($entityName, $action, $zone);

        return $this->makeUrl($urlElement, null, $removeCycling);
    }

    /**
     * @param string $entityName
     * @param string $zone
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getCreateEntityUrl(string $entityName, string $zone): string
    {
        return $this->getEntityNameUrl($entityName, CrudlsActionsInterface::ACTION_CREATE, $zone);
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
     * @param string $entityName
     * @param string $zone
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     */
    public function getListEntityUrl(string $entityName, string $zone): string
    {
        return $this->getEntityNameUrl($entityName, CrudlsActionsInterface::ACTION_LIST, $zone);
    }

    /**
     * @param string $entityName
     * @param string $zone
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     */
    public function getSearchEntityUrl(string $entityName, string $zone): string
    {
        return $this->getEntityNameUrl($entityName, CrudlsActionsInterface::ACTION_SEARCH, $zone);
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

    public function detectDummyTarget(DummyModelInterface $element): UrlElementInterface
    {
        // Keep current element if forward target defined
        if ($element->getForwardTarget()) {
            return $element;
        }

        $redirectElement = $element;

        // Process chained dummies to prevent multiple redirects in browser
        do {
            $redirectTarget = $redirectElement->getRedirectTarget();

            $redirectElement = $redirectTarget
                ? $this->getUrlElementByCodename($redirectTarget)
                : null;
        } while ($redirectElement && $redirectElement instanceof DummyModelInterface && $redirectElement->getRedirectTarget());

        // Fallback to parent if redirect is not defined
        if (!$redirectElement) {
            $redirectElement = $this->getParentIFace($element);
        }

        return $redirectElement;
    }

    private function getParentIFace(UrlElementInterface $model): UrlElementInterface
    {
        // Find nearest IFace
        foreach ($this->tree->getReverseBreadcrumbsIterator($model) as $parent) {
            if ($parent instanceof IFaceModelInterface) {
                return $parent;
            }
        }

        $parent = $this->tree->getParent($model);

        // Redirect root dummies to default element
        if (!$parent) {
            return $this->tree->getDefault();
        }

        throw new UrlElementException('No IFace found for Dummy with URI ":uri" and parent ":parent"', [
            ':uri'    => $model->getUri(),
            ':parent' => $model->getParentCodename() ?: 'root',
        ]);
    }

    private function makeAbsoluteUrl(string $relativeUrl): string
    {
        return (string)$this->appConfig->getBaseUri()->withPath($relativeUrl);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    private function makeUrlElementUri(UrlElementInterface $model, UrlContainerInterface $params): string
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

        return $behaviour->makeUri($model, $params);
    }
}
