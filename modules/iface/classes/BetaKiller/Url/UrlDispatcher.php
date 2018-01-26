<?php
namespace BetaKiller\Url;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\Exception\IFaceMissingUrlException;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceModelsStack;
use BetaKiller\IFace\IFaceModelTree;
use BetaKiller\MessageBus\EventBus;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use HTTP;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class UrlDispatcher implements LoggerAwareInterface
{
    use LoggerHelperTrait;

    /**
     * Defines default uri for index element (this used if root IFace has dynamic url behaviour)
     */
    public const DEFAULT_URI = 'index';

    /**
     * Current IFace stack
     *
     * @var \BetaKiller\IFace\IFaceModelsStack
     */
    private $ifaceStack;

    /**
     * Current Url parameters
     *
     * @var \BetaKiller\Url\UrlContainerInterface
     */
    private $urlParameters;

    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $cache;

    /**
     * @var \BetaKiller\MessageBus\EventBus
     */
    private $eventBus;

    /**
     * @Inject
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

    /**
     * @var \BetaKiller\Url\Behaviour\UrlBehaviourFactory
     */
    private $behaviourFactory;

    /**
     * @var \BetaKiller\Helper\UrlHelper
     */
    private $urlHelper;

    /**
     * @var \BetaKiller\IFace\IFaceModelTree
     */
    private $tree;

    /**
     * @param \BetaKiller\IFace\IFaceModelsStack            $stack
     * @param \BetaKiller\IFace\IFaceModelTree              $tree
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory $behaviourFactory
     * @param \BetaKiller\Url\UrlContainerInterface         $parameters
     * @param \Psr\SimpleCache\CacheInterface               $cache
     * @param \BetaKiller\MessageBus\EventBus               $eventBus
     * @param \BetaKiller\Helper\UrlHelper                  $urlHelper
     * @param \BetaKiller\Helper\AclHelper                  $aclHelper
     */
    public function __construct(
        IFaceModelsStack $stack,
        IFaceModelTree $tree,
        UrlBehaviourFactory $behaviourFactory,
        UrlContainerInterface $parameters,
        CacheInterface $cache,
        EventBus $eventBus,
        UrlHelper $urlHelper,
        AclHelper $aclHelper
    ) {
        $this->ifaceStack       = $stack;
        $this->urlParameters    = $parameters;
        $this->cache            = $cache;
        $this->eventBus         = $eventBus;
        $this->aclHelper        = $aclHelper;
        $this->behaviourFactory = $behaviourFactory;
        $this->urlHelper        = $urlHelper;
        $this->tree             = $tree;
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param string $uri
     * @param string $ip
     *
     * @return \BetaKiller\IFace\IFaceModelInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\IFace\Exception\IFaceMissingUrlException
     * @throws \BetaKiller\IFace\Exception\IFaceStackException
     * @throws \BetaKiller\MessageBus\MessageBusException
     * @throws \Spotman\Acl\Exception
     */
    public function process(string $uri, string $ip): IFaceModelInterface
    {
        // Prevent XSS via URL
        $uri = htmlspecialchars(strip_tags($uri), ENT_QUOTES);

        $path = parse_url($uri, PHP_URL_PATH);

        $cacheKey = $this->getUrlCacheKey($uri);

        // Check cache for stack and url params for current URL
        if (!$this->restoreDataFromCache($cacheKey)) {
            $this->parseUriPath($path);

            // Cache stack + url parameters (between HTTP requests) for current URL
            $this->storeDataInCache($cacheKey);
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        // Emit event about successful url parsing
        $this->eventBus->emit(new UrlDispatchedEvent($uri, $this->urlParameters, $ip, $referer));

        // Get latest IFace model
        return $this->ifaceStack->getCurrent();
    }

    /**
     * Performs parsing of requested url
     * Returns IFace
     *
     * @param string $uri
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \Spotman\Acl\Exception
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\IFace\Exception\IFaceStackException
     * @throws \BetaKiller\IFace\Exception\IFaceMissingUrlException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function parseUriPath(string $uri): void
    {
        // Creating URL iterator
        $urlIterator = new UrlPathIterator($uri);

        $parentModel = null;

        // Dispatch childs
        // Loop through every uri part and initialize it`s iface
        do {
            $ifaceModel = null;

            try {
                if ($urlIterator->rootRequested()) {
                    $ifaceModel = $this->tree->getDefault();

                    $this->processUrlBehaviour($ifaceModel, $urlIterator);
                } else {
                    $ifaceModel = $this->parseUriLayer($urlIterator, $parentModel);
                }
            } catch (UrlBehaviourException $e) {
                // Log this exception and keep processing
                $this->logException($this->logger, $e);
            }

            // Throw IFaceMissingUrlException so we can forward user to parent iface or custom 404 page
            if (!$ifaceModel) {
                $this->throwMissingUrlException($urlIterator, $parentModel);
            }

            $parentModel = $ifaceModel;

            $this->pushToStack($ifaceModel);

            $urlIterator->next();
        } while ($urlIterator->valid());
    }

    /**
     * @param \BetaKiller\Url\UrlPathIterator            $it
     * @param \BetaKiller\IFace\IFaceModelInterface|null $parent
     *
     * @throws \BetaKiller\IFace\Exception\IFaceMissingUrlException
     */
    private function throwMissingUrlException(UrlPathIterator $it, IFaceModelInterface $parent = null): void
    {
        throw new IFaceMissingUrlException($it->current(), $parent);
    }

    /**
     * Performs iface search by uri part(s) in iface layer
     *
     * @param UrlPathIterator                       $it
     * @param \BetaKiller\IFace\IFaceModelInterface $parentIFaceModel
     *
     * @return IFaceModelInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\IFace\Exception\IFaceMissingUrlException
     * @throws \BetaKiller\Url\UrlBehaviourException
     */
    private function parseUriLayer(
        UrlPathIterator $it,
        IFaceModelInterface $parentIFaceModel = null
    ): ?IFaceModelInterface {
        $layer = [];

        try {
            $layer = $parentIFaceModel
                ? $this->tree->getChildren($parentIFaceModel)
                : $this->tree->getRoot();

            if (!$layer) {
                throw new IFaceException('Empty layer for IFace :codename', [
                    ':codename' => $parentIFaceModel ? $parentIFaceModel->getCodename() : 'root',
                ]);
            }
        } catch (IFaceException $e) {
            // Log this exception and keep processing
            $this->logException($this->logger, $e);

            $parentUrl = $parentIFaceModel
                ? $this->urlHelper->makeIFaceUrl($parentIFaceModel, $this->urlParameters, false)
                : null;

            if ($parentUrl) {
                $this->redirect($parentUrl);
            } else {
                $this->throwMissingUrlException($it, $parentIFaceModel);
            }
        }

        return $this->selectIFaceModel($layer, $it);
    }

    private function redirect($url, $code = null): void
    {
        // TODO PSR-7 Create interface for redirect() method, use it in Response and send Response instance to $this via DI
        HTTP::redirect($url, $code ?: 302);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface[] $models
     *
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     */
    private function sortModelsLayer(array $models): array
    {
        $fixed   = [];
        $dynamic = [];

        foreach ($models as $model) {
            if ($model->hasDynamicUrl() || $model->hasTreeBehaviour()) {
                $dynamic[] = $model;
            } else {
                $fixed[] = $model;
            }
        }

        // Fixed URLs first, dynamic URLs last
        return array_merge($fixed, $dynamic);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface[] $models
     * @param UrlPathIterator                         $it
     *
     * @return \BetaKiller\IFace\IFaceModelInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlBehaviourException
     */
    private function selectIFaceModel(array $models, UrlPathIterator $it): ?IFaceModelInterface
    {
        // Put fixed urls first
        $models = $this->sortModelsLayer($models);

        foreach ($models as $model) {
            if ($this->processUrlBehaviour($model, $it)) {
                return $model;
            }
        }

        // Nothing found
        return null;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     * @param \BetaKiller\Url\UrlPathIterator       $it
     *
     * @return bool
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlBehaviourException
     */
    private function processUrlBehaviour(IFaceModelInterface $model, UrlPathIterator $it): bool
    {
        $behaviour = $this->behaviourFactory->fromIFaceModel($model);

        return $behaviour->parseUri($model, $it, $this->urlParameters);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $ifaceModel
     *
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\IFace\Exception\IFaceStackException
     * @throws \Spotman\Acl\Exception
     */
    private function pushToStack(IFaceModelInterface $ifaceModel): void
    {
        $this->checkIFaceAccess($ifaceModel);
        $this->ifaceStack->push($ifaceModel);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    private function checkIFaceAccess(IFaceModelInterface $model): void
    {
        // Force authorization for non-public zones before security check
        $this->aclHelper->forceAuthorizationIfNeeded($model);

        if (!$this->aclHelper->isIFaceAllowed($model, $this->urlParameters)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * @param string $cacheKey
     *
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function storeDataInCache(string $cacheKey): bool
    {
        $stackData  = $this->ifaceStack->getCodenames();
        $paramsData = $this->urlParameters->getAllParameters();

        foreach ($paramsData as $param) {
            if (!$this->isParameterSerializable($param)) {
                $this->logger->debug('Skip caching non-serializable parameter');

                return false;
            }
        }

        $this->cache->set($cacheKey, serialize([
            'stack'      => $stackData,
            'parameters' => $paramsData,
        ]));

        return true;
    }

    /**
     * @param string $cacheKey
     *
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function restoreDataFromCache(string $cacheKey): bool
    {
        $serializedData = $this->cache->get($cacheKey);

        $data = unserialize($serializedData, [
            IFaceModelInterface::class,
            DispatchableEntityInterface::class,
        ]);

        if (!$data) {
            return false;
        }

        if (!\is_array($data)) {
            // Log and keep processing as no cache was found
            $this->logger->warning('Cached UrlDispatcher data is incorrect', ['cachedData' => print_r($data, true)]);

            return false;
        }

        /** @var array $stackData */
        $stackData = $data['stack'];

        /** @var \BetaKiller\Url\UrlParameterInterface[] $paramsData */
        $paramsData = $data['parameters'];

        try {
            // Restore url parameters first so iface access can be checked
            foreach ($paramsData as $key => $value) {
                if (!($value instanceof UrlParameterInterface)) {
                    throw new UrlDispatcherException('Cached data for url parameters is incorrect');
                }

                if (!$this->isParameterSerializable($value)) {
                    $this->logger->debug('Skip unpacking data from non-serializable parameter');

                    return false;
                }

                $this->urlParameters->setParameter($value);
            }

            // Restore ifaces and push them into stack (with access check)
            foreach ($stackData as $ifaceCodename) {
                $ifaceModel = $this->tree->getByCodename($ifaceCodename);
                $this->pushToStack($ifaceModel);
            }

            return true;
        } catch (\Throwable $e) {
            // Log and keep processing as no cache was found
            $this->logException($this->logger, $e, 'Error on unpacking UrlDispatcher data');

            // Wipe the cached data to prevent errors
            $this->cache->delete($cacheKey);
        }

        return false;
    }

    private function isParameterSerializable(UrlParameterInterface $param): bool
    {
        // TODO Deal with this (remove or refactor to using some kind of interface)
        return (bool)$param;
    }

    private function getUrlCacheKey(string $url): string
    {
        return 'urlDispatcher.'.md5($url);
    }
}
