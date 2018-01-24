<?php
namespace BetaKiller\Url;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\Exception\IFaceMissingUrlException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceProvider;
use BetaKiller\IFace\IFaceStack;
use BetaKiller\MessageBus\EventBus;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use HTTP;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

//use BetaKiller\IFace\HasCustomUrlBehaviourInterface;

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
     * @var \BetaKiller\IFace\IFaceStack
     */
    private $ifaceStack;

    /**
     * Current Url parameters
     *
     * @var \BetaKiller\Url\UrlContainerInterface
     */
    private $urlParameters;

    /**
     * @var IFaceProvider
     */
    private $ifaceProvider;

    /**
     * @var \BetaKiller\Url\UrlDispatcherCacheInterface
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
     * @param \BetaKiller\IFace\IFaceStack                  $stack
     * @param \BetaKiller\IFace\IFaceProvider               $provider
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory $behaviourFactory
     * @param \BetaKiller\Url\UrlContainerInterface         $parameters
     * @param \BetaKiller\Url\UrlDispatcherCacheInterface   $cache
     * @param \BetaKiller\MessageBus\EventBus               $eventBus
     * @param \BetaKiller\Helper\AclHelper                  $aclHelper
     */
    public function __construct(
        IFaceStack $stack,
        IFaceProvider $provider,
        UrlBehaviourFactory $behaviourFactory,
        UrlContainerInterface $parameters,
        UrlDispatcherCacheInterface $cache,
        EventBus $eventBus,
        AclHelper $aclHelper
    ) {
        $this->ifaceStack       = $stack;
        $this->ifaceProvider    = $provider;
        $this->urlParameters    = $parameters;
        $this->cache            = $cache;
        $this->eventBus         = $eventBus;
        $this->aclHelper        = $aclHelper;
        $this->behaviourFactory = $behaviourFactory;
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
     * @return \BetaKiller\IFace\IFaceInterface
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\IFace\Exception\IFaceMissingUrlException
     * @throws \BetaKiller\IFace\Exception\IFaceStackException
     * @throws \BetaKiller\MessageBus\MessageBusException
     * @throws \Spotman\Acl\Exception
     */
    public function process(string $uri, string $ip): IFaceInterface
    {
        // Prevent XSS via URL
        $uri = htmlspecialchars(strip_tags($uri), ENT_QUOTES);

        $path = parse_url($uri, PHP_URL_PATH);

        // Check cache for stack and url params for current URL
        if (!$this->restoreDataFromCache($uri)) {
            $this->parseUriPath($path);

            // Cache stack + url parameters (between HTTP requests) for current URL
            $this->storeDataInCache($uri);
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        // Emit event about successful url parsing
        $this->eventBus->emit(new UrlDispatchedEvent($uri, $ip, $referer));

        // Return last IFace
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

        $parentIFace   = null;
        $ifaceInstance = null;

        // Dispatch childs
        // Loop through every uri part and initialize it`s iface
        do {
            $ifaceInstance = null;

            try {
                if ($urlIterator->rootRequested()) {
                    $ifaceInstance = $this->ifaceProvider->getDefault();

                    $this->processUrlBehaviour($ifaceInstance->getModel(), $urlIterator);
                } else {
                    $ifaceInstance = $this->parseUriLayer($urlIterator, $parentIFace);
                }
            } catch (UrlBehaviourException $e) {
                // Log this exception and keep processing
                $this->logException($this->logger, $e);
            }

            // Throw IFaceMissingUrlException so we can forward user to parent iface or custom 404 page
            if (!$ifaceInstance) {
                $this->throwMissingUrlException($urlIterator, $parentIFace);
            }

            $parentIFace = $ifaceInstance;

            $this->pushToStack($ifaceInstance);

            $urlIterator->next();
        } while ($urlIterator->valid());
    }

    /**
     * @param \BetaKiller\Url\UrlPathIterator       $it
     * @param \BetaKiller\IFace\IFaceInterface|null $parentIFace
     *
     * @throws \BetaKiller\IFace\Exception\IFaceMissingUrlException
     */
    private function throwMissingUrlException(UrlPathIterator $it, IFaceInterface $parentIFace = null): void
    {
        throw new IFaceMissingUrlException($it->current(), $parentIFace);
    }

    /**
     * Performs iface search by uri part(s) in iface layer
     *
     * @param UrlPathIterator     $it
     * @param IFaceInterface|NULL $parentIFace
     *
     * @return IFaceInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\IFace\Exception\IFaceMissingUrlException
     * @throws \BetaKiller\Url\UrlBehaviourException
     */
    private function parseUriLayer(UrlPathIterator $it, IFaceInterface $parentIFace = null): ?IFaceInterface
    {
        $layer = [];

        try {
            $layer = $this->ifaceProvider->getModelsLayer($parentIFace);
        } catch (IFaceException $e) {
            // Log this exception and keep processing
            $this->logException($this->logger, $e);

            $parentUrl = $parentIFace ? $parentIFace->url($this->urlParameters, false) : null;

            if ($parentUrl) {
                $this->redirect($parentUrl);
            } else {
                $this->throwMissingUrlException($it, $parentIFace);
            }
        }

        $model = $this->selectIFaceModel($layer, $it);

        return $model ? $this->ifaceProvider->fromModel($model) : null;
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
     * @throws IFaceException
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

        // TODO Move this into TreeModel internal validation
        if (\count($dynamic) > 1) {
            throw new IFaceException('Layer must have only one IFace with dynamic dispatching');
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
     * @throws \BetaKiller\IFace\Exception\IFaceException
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
     * @param IFaceInterface $iface
     *
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \Spotman\Acl\Exception
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\IFace\Exception\IFaceStackException
     */
    private function pushToStack(IFaceInterface $iface): void
    {
        $this->checkIFaceAccess($iface);
        $this->ifaceStack->push($iface);
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    private function checkIFaceAccess(IFaceInterface $iface): void
    {
        // Force authorization for non-public zones before security check
        $this->aclHelper->forceAuthorizationIfNeeded($iface->getModel());

        if (!$this->aclHelper->isIFaceAllowed($iface->getModel(), $this->urlParameters)) {
            throw new AccessDeniedException();
        }
    }

    private function storeDataInCache(string $url): bool
    {
        $stackData  = $this->ifaceStack->getCodenames();
        $paramsData = $this->urlParameters->getAllParameters();

        foreach ($paramsData as $param) {
            if (!$this->isParameterSerializable($param)) {
                $this->logger->debug('Skip caching non-serializable parameter');

                return false;
            }
        }

        $cacheKey = $this->getUrlCacheKey($url);

        $this->cache->set($cacheKey, [
            'stack'      => $stackData,
            'parameters' => $paramsData,
        ]);

        return true;
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    private function restoreDataFromCache(string $url): bool
    {
        $cacheKey = $this->getUrlCacheKey($url);
        $data     = $this->cache->get($cacheKey);

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
                $iface = $this->ifaceProvider->fromCodename($ifaceCodename);
                $this->pushToStack($iface);
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
        return 'urlDispatcher.'.$url;
    }
}
