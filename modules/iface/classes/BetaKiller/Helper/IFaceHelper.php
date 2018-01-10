<?php
namespace BetaKiller\Helper;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Exception;
use BetaKiller\IFace\CrudlsActionsInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceProvider;
use BetaKiller\IFace\IFaceStack;
use BetaKiller\IFace\Url\UrlContainerInterface;
use BetaKiller\IFace\Url\UrlDataSourceInterface;
use BetaKiller\IFace\Url\UrlDispatcher;
use BetaKiller\IFace\Url\UrlPrototype;
use BetaKiller\IFace\Url\UrlPrototypeHelper;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\IFaceZone;
use BetaKiller\View\IFaceView;
use Spotman\Api\ApiMethodResponse;

class IFaceHelper
{
    /**
     * @var \BetaKiller\IFace\IFaceStack
     */
    private $stack;

    /**
     * @var \BetaKiller\View\IFaceView
     */
    private $view;

    /**
     * @var \BetaKiller\Helper\UrlContainerHelper
     */
    private $paramsHelper;

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * @var \BetaKiller\IFace\Url\UrlPrototypeHelper
     */
    private $urlHelper;

    /**
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

    /**
     * @var \BetaKiller\IFace\IFaceProvider
     */
    private $provider;

    /**
     * IFaceHelper constructor.
     *
     * @param \BetaKiller\View\IFaceView               $view
     * @param \BetaKiller\IFace\IFaceStack             $stack
     * @param \BetaKiller\Helper\UrlContainerHelper    $paramsHelper
     * @param \BetaKiller\Config\AppConfigInterface    $appConfig
     * @param \BetaKiller\IFace\IFaceProvider          $provider
     * @param \BetaKiller\IFace\Url\UrlPrototypeHelper $urlHelper
     * @param \BetaKiller\Helper\AclHelper             $aclHelper
     */
    public function __construct(
        IFaceView $view,
        IFaceStack $stack,
        UrlContainerHelper $paramsHelper,
        AppConfigInterface $appConfig,
        IFaceProvider $provider,
        UrlPrototypeHelper $urlHelper,
        AclHelper $aclHelper
    ) {
        $this->view         = $view;
        $this->stack        = $stack;
        $this->paramsHelper = $paramsHelper;
        $this->appConfig    = $appConfig;
        $this->provider     = $provider;
        $this->urlHelper    = $urlHelper;
        $this->aclHelper    = $aclHelper;
    }

    public function getCurrentIFace(): ?IFaceInterface
    {
        return $this->stack->getCurrent();
    }

    public function isCurrentIFaceAction(string $name): bool
    {
        $currentIFace  = $this->getCurrentIFace();
        $currentAction = $currentIFace ? $currentIFace->getEntityActionName() : null;

        return $currentAction === $name;
    }

    public function isCurrentIFaceZone(string $zone): bool
    {
        $currentIFace = $this->getCurrentIFace();
        $currentZone  = $currentIFace ? $currentIFace->getZoneName() : null;

        return $currentZone === $zone;
    }

    public function isCurrentIFace(IFaceInterface $iface, UrlContainerInterface $params = null): bool
    {
        return $this->stack->isCurrent($iface, $params);
    }

    public function isInStack(IFaceInterface $iface): bool
    {
        return $this->stack->has($iface);
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @return string
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function renderIFace(IFaceInterface $iface): string
    {
        // Getting IFace View instance and rendering
        return $this->view->render($iface);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function createIFaceFromCodename(string $codename): IFaceInterface
    {
        return $this->provider->fromCodename($codename);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function createIFaceFromModel(IFaceModelInterface $model): IFaceInterface
    {
        return $this->provider->fromModel($model);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $action
     * @param string|null                                   $zone
     *
     * @return string
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getEntityUrl(DispatchableEntityInterface $entity, string $action, ?string $zone = null): string
    {
        if (!$zone) {
            $currentIFace = $this->getCurrentIFace();

            if (!$currentIFace) {
                throw new Exception('IFace zone must be specified');
            }

            // Fetch zone from current IFace
            $zone = $currentIFace->getZoneName();
        }

        // Search for IFace with provided entity, action and zone
        $iface = $this->provider->getByEntityActionAndZone($entity, $action, $zone);

        // TODO Create ResolvingUrlParameters instance from current entity
        // TODO Fetch linked entities from current entity on-demand

        $params = $this->paramsHelper->createEmpty();
        $params->setParameter($entity);
        $entity->presetLinkedEntities($params);

        return $iface->url($params);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param null|string                                   $zone
     *
     * @return string
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getCreateEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_CREATE, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param null|string                                   $zone
     *
     * @return string
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getReadEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_READ, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param null|string                                   $zone
     *
     * @return string
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getUpdateEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_UPDATE, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param null|string                                   $zone
     *
     * @return string
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getDeleteEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_DELETE, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param null|string                                   $zone
     *
     * @return string
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getListEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_LIST, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param null|string                                   $zone
     *
     * @return string
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getSearchEntityUrl(DispatchableEntityInterface $entity, ?string $zone = null): string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_SEARCH, $zone);
    }

    /**
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     *
     * @return null|string
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getPreviewEntityUrl(DispatchableEntityInterface $entity): ?string
    {
        return $this->getEntityUrl($entity, CrudlsActionsInterface::ACTION_READ, IFaceZone::PREVIEW_ZONE);
    }

    /**
     * @param \Spotman\Api\ApiMethodResponse   $response
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @return mixed
     */
    public function processApiResponse(ApiMethodResponse $response, IFaceInterface $iface)
    {
        $iface->setLastModified($response->getLastModified());

        return $response->getData();
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @throws \Exception
     */
    public function setExpiresInPast(IFaceInterface $iface): void
    {
        // No caching for admin zone
        $interval         = new \DateInterval('PT1H');
        $interval->invert = 1;

        $iface->setExpiresInterval($interval);
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface                 $iface
     * @param \BetaKiller\IFace\Url\UrlContainerInterface|null $urlContainer
     * @param bool|null                                        $removeCyclingLinks
     * @param bool|null                                        $withDomain
     *
     * @return string
     * @throws \BetaKiller\IFace\Url\UrlPrototypeException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function makeUrl(
        IFaceInterface $iface,
        UrlContainerInterface $urlContainer = null,
        ?bool $removeCyclingLinks = null,
        ?bool $withDomain = null
    ): string {
        $removeCyclingLinks = $removeCyclingLinks ?? true;
        $withDomain         = $withDomain ?? true;

        if ($removeCyclingLinks && $this->isCurrentIFace($iface, $urlContainer)) {
            return $this->appConfig->getCircularLinkHref();
        }

        $parts   = [];
        $current = $iface;

        $parent = null;

        do {
            $uri = $this->makeUri($current, $urlContainer);

            if (!$uri) {
                throw new IFaceException('Can not make URI for :codename IFace', [
                    ':codename' => $current->getCodename(),
                ]);
            }

            if ($uri === UrlDispatcher::DEFAULT_URI && $current->isDefault()) {
                $uri = null;
            }

            $parts[] = $uri;
            $parent  = $current->getParent();
            $current = $parent;
        } while ($parent);

        $path = '/'.implode('/', array_reverse($parts));

        if ($this->appConfig->isTrailingSlashEnabled()) {
            // Add trailing slash before query parameters
            $split    = explode('?', $path, 2);
            $split[0] .= '/';
            $path     = implode('?', $split);
        }

        return $withDomain ? \URL::site($path, true) : $path;
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface                 $iface
     * @param \BetaKiller\IFace\Url\UrlContainerInterface|null $params
     *
     * @return string
     * @throws \BetaKiller\IFace\Url\UrlPrototypeException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function makeUri(IFaceInterface $iface, UrlContainerInterface $params = null): string
    {
        // Allow IFace to add custom url generating logic
        $uri   = $iface->getUri();
        $model = $iface->getModel();

        if (!$uri) {
            throw new IFaceException('IFace :codename must have uri', [':codename' => $iface->getCodename()]);
        }

        // Static IFaces has raw uri value
        if (!$model->hasDynamicUrl()) {
            return $uri;
        }

        return $this->urlHelper->getCompiledPrototypeValue($uri, $params, $model->hasTreeBehaviour());
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface            $iface
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $params
     *
     * @todo This method calculates only urls for current iface but pair "uri => urlParameter" is needed for traversing over url tree and creating full url map
     * @return string[]
     * @throws \Spotman\Acl\Exception
     * @throws \BetaKiller\IFace\Url\UrlPrototypeException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getPublicAvailableUrls(IFaceInterface $iface, UrlContainerInterface $params): array
    {
        if (!$iface->getModel()->hasDynamicUrl()) {
            // Make static URL
            return [$this->makeAvailableUrl($iface, $params)];
        }

        return $this->getDynamicModelAvailableUrls($iface, $params);
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface            $iface
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $params
     *
     * @return string[]
     * @throws \Spotman\Acl\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\IFace\Url\UrlPrototypeException
     */
    private function getDynamicModelAvailableUrls(IFaceInterface $iface, UrlContainerInterface $params): array
    {
        $prototype  = $this->urlHelper->fromIFaceUri($iface);
        $dataSource = $this->urlHelper->getDataSourceInstance($prototype);

        $this->urlHelper->validatePrototypeModelKey($prototype, $dataSource);

        $urls = [];

        $this->collectDataSourceAvailableUrls($iface, $dataSource, $prototype, $params, $urls);

        return array_filter($urls);
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface             $iface
     * @param \BetaKiller\IFace\Url\UrlDataSourceInterface $dataSource
     * @param \BetaKiller\IFace\Url\UrlPrototype           $prototype
     * @param \BetaKiller\IFace\Url\UrlContainerInterface  $params
     * @param array                                        $urls
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    private function collectDataSourceAvailableUrls(
        IFaceInterface $iface,
        UrlDataSourceInterface $dataSource,
        UrlPrototype $prototype,
        UrlContainerInterface $params,
        array &$urls
    ): void {
        $items = $prototype->hasIdKey()
            ? $dataSource->getAll()
            : $dataSource->getItemsHavingUrlKey($params);

        foreach ($items as $item) {
            // Save current item to parameters registry
            $params->setParameter($item, true);

            // Make dynamic URL
            $url = $this->makeAvailableUrl($iface, $params);

            if (!$url) {
                // No tree traversal if current url is not allowed
                continue;
            }

            $urls[] = $url;

            // Recursion for trees
            if ($iface->getModel()->hasTreeBehaviour()) {
                // Recursion for tree behaviour
                $this->collectDataSourceAvailableUrls($iface, $dataSource, $prototype, $params, $urls);
            }
        }
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface                 $iface
     * @param \BetaKiller\IFace\Url\UrlContainerInterface|null $params
     *
     * @return null|string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    private function makeAvailableUrl(IFaceInterface $iface, UrlContainerInterface $params = null): ?string
    {
        if (!$this->aclHelper->isIFaceAllowed($iface, $params)) {
            return null;
        }

        return $this->makeUrl($iface, $params, false); // Disable cycling links removing
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     * @deprecated Do not use direct parent search, use special iterators for traversing up and down the IFaceTree
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getIFaceParent(IFaceInterface $iface): ?IFaceInterface
    {
        return $this->provider->getParent($iface);
    }
}
