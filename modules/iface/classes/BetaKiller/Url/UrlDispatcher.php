<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Auth\AccessDeniedException;
use BetaKiller\Event\MissingUrlEvent;
use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\Exception\NotFoundHttpException;
use BetaKiller\Exception\SeeOtherHttpException;
use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Helper\UrlHelper;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Url\Behaviour\UrlBehaviourException;
use BetaKiller\Url\Behaviour\UrlBehaviourFactory;
use BetaKiller\Url\Container\UrlContainer;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\Parameter\UrlParameterInterface;
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

    public const CACHE_TTL = 86400; // 1 day

    /**
     * Current IFace stack
     *
     * @var \BetaKiller\Url\UrlElementStack
     */
    private $urlElementStack;

    /**
     * Current Url parameters
     *
     * @var \BetaKiller\Url\Container\UrlContainerInterface
     */
    private $urlParameters;

    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $cache;

    /**
     * @var \BetaKiller\MessageBus\EventBusInterface
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
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @param \BetaKiller\Url\UrlElementStack                 $stack
     * @param \BetaKiller\Url\UrlElementTreeInterface         $tree
     * @param \BetaKiller\Url\Behaviour\UrlBehaviourFactory   $behaviourFactory
     * @param \BetaKiller\Url\Container\UrlContainerInterface $parameters
     * @param \Psr\SimpleCache\CacheInterface                 $cache
     * @param \BetaKiller\MessageBus\EventBusInterface        $eventBus
     * @param \BetaKiller\Helper\UrlHelper                    $urlHelper
     * @param \BetaKiller\Helper\AclHelper                    $aclHelper
     */
    public function __construct(
        UrlElementStack $stack,
        UrlElementTreeInterface $tree,
        UrlBehaviourFactory $behaviourFactory,
        UrlContainerInterface $parameters,
        CacheInterface $cache,
        EventBusInterface $eventBus,
        UrlHelper $urlHelper,
        AclHelper $aclHelper
    ) {
        $this->urlElementStack  = $stack;
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
     * @param string      $uri
     * @param string      $ip
     *
     * @param null|string $referrer
     *
     * @return \BetaKiller\Url\UrlElementInterface
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Exception\SeeOtherHttpException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\MessageBus\MessageBusException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Spotman\Acl\Exception
     */
    public function process(string $uri, string $ip, ?string $referrer): UrlElementInterface
    {
        // Prevent XSS via URL
        $uri = htmlspecialchars($uri, ENT_QUOTES);

        $path = parse_url($uri, PHP_URL_PATH);

        $cacheKey = $this->getUrlCacheKey($uri);

        // Check cache for stack and url params for current URL
        if (!$this->restoreDataFromCache($cacheKey)) {
            $this->processUriPath($path, $ip, $referrer);

            // Cache stack + url parameters (between HTTP requests) for current URL
            $this->storeDataInCache($cacheKey);
        }

        // Emit event about successful url parsing
        $this->eventBus->emit(new UrlDispatchedEvent($uri, $this->urlParameters, $ip, $referrer));

        // Get latest IFace model
        return $this->urlElementStack->getCurrent();
    }

    /**
     * @param string      $uri
     * @param string      $ip
     * @param null|string $referrer
     *
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Exception\SeeOtherHttpException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\MessageBus\MessageBusException
     * @throws \Spotman\Acl\Exception
     */
    private function processUriPath(string $uri, string $ip, ?string $referrer): void
    {
        try {
            $this->parseUriPath($uri, $this->urlElementStack, $this->urlParameters);

            return;
        } catch (MissingUrlElementException $e) {
            $redirectToUrl = $e->getRedirectTo();
            $parentModel   = $e->getParentUrlElement();

            if ($redirectToUrl) {
                // Missing but see other
                $this->eventBus->emit(new MissingUrlEvent($uri, $parentModel, $ip, $referrer, $redirectToUrl));

                throw new SeeOtherHttpException($redirectToUrl);
            }

            // Simply not found
            $this->eventBus->emit(new MissingUrlEvent($uri, $parentModel, $ip, $referrer));

            throw new NotFoundHttpException;
        } catch (UrlBehaviourException | IFaceException $e) {
            // Log this exception and keep processing
            $this->logException($this->logger, $e);

            // Nothing found
            throw new NotFoundHttpException;
        }
    }

    /**
     * Performs parsing of requested url
     * Returns IFace
     *
     * @param string                                          $uri
     *
     * @param \BetaKiller\Url\UrlElementStack                 $stack
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParams
     *
     * @return void
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\Url\MissingUrlElementException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     * @throws \Spotman\Acl\Exception
     */
    private function parseUriPath(string $uri, UrlElementStack $stack, UrlContainerInterface $urlParams): void
    {
        // Creating URL iterator
        $urlIterator = new UrlPathIterator($uri);

        $parentModel = null;

        // Dispatch childs
        // Loop through every uri part and initialize it`s iface
        do {
            $urlElement = $this->detectUrlElement($urlIterator, $urlParams, $parentModel);

            // Try to find custom 404 page
            if (!$urlElement) {
                // Search for 404 iface in parent branch
                $urlElement = $this->searchFor404IFaceInBranch($parentModel);
            }

            // No IFace found => throw HTTP 404 exception
            if (!$urlElement) {
                throw new MissingUrlElementException($parentModel);
            }

            $parentModel = $urlElement;

            $this->pushToStack($urlElement, $stack, $urlParams);

            $urlIterator->next();
        } while ($urlIterator->valid());
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    public function isValidUrl(string $url): bool
    {
        $params = new UrlContainer();
        $stack  = new UrlElementStack($params);

        try {
            $path = parse_url($url, PHP_URL_PATH);
            $this->parseUriPath($path, $stack, $params);

            return true;
        } /** @noinspection BadExceptionsProcessingInspection */ catch (\Throwable $e) {
            // No logging in this case
            return false;
        }
    }

    /**
     * @param \BetaKiller\Url\UrlPathIterator                 $it
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParameters
     * @param \BetaKiller\Url\UrlElementInterface|null        $parentModel
     *
     * @return \BetaKiller\Url\IFaceModelInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\Url\MissingUrlElementException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     */
    private function detectUrlElement(
        UrlPathIterator $it,
        UrlContainerInterface $urlParameters,
        ?UrlElementInterface $parentModel
    ): ?UrlElementInterface {
        if ($it->rootRequested()) {
            $defaultIFace = $this->tree->getDefault();

            $this->processUrlBehaviour($defaultIFace, $it, $urlParameters);

            return $defaultIFace;
        }

        // Get child IFaces
        $layer = $parentModel
            ? $this->tree->getChildren($parentModel)
            : $this->tree->getRoot();

        if ($layer) {
            // Search for appropriate model in current layer
            return $this->selectUrlElementModel($layer, $it, $urlParameters);
        }

        // Empty layer but parent model exists
        if ($parentModel) {
            $parentUrl = $this->urlHelper->makeUrl($parentModel, $this->urlParameters, false);

            // Force redirect to parent URL if no child IFaces found (in case of bad copy-paste)
            throw new MissingUrlElementException($parentModel, $parentUrl);
        }

        // No parent and empty layer => no IFace
        return null;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface|null $parentModel
     *
     * @return \BetaKiller\Url\IFaceModelInterface|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function searchFor404IFaceInBranch(?UrlElementInterface $parentModel = null): ?UrlElementInterface
    {
        $items = $parentModel
            ? $this->tree->getReverseBreadcrumbsIterator($parentModel)
            : $this->tree->getRoot();

        foreach ($items as $item) {
            if (strpos($item->getCodename(), 'Error404') !== false) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface[] $models
     *
     * @return \BetaKiller\Url\UrlElementInterface[]
     */
    private function sortModelsLayer(array $models): array
    {
        $fixed   = [];
        $dynamic = [];

        foreach ($models as $model) {
            if ($model instanceof IFaceModelInterface && ($model->hasDynamicUrl() || $model->hasTreeBehaviour())) {
                $dynamic[] = $model;
            } else {
                $fixed[] = $model;
            }
        }

        // Fixed URLs first, dynamic URLs last
        return array_merge($fixed, $dynamic);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface[]           $models
     * @param UrlPathIterator                                 $it
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParameters
     *
     * @return \BetaKiller\Url\UrlElementInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     */
    private function selectUrlElementModel(
        array $models,
        UrlPathIterator $it,
        UrlContainerInterface $urlParameters
    ): ?UrlElementInterface {
        // Put fixed urls first
        $models = $this->sortModelsLayer($models);

        foreach ($models as $model) {
            if ($this->processUrlBehaviour($model, $it, $urlParameters)) {
                return $model;
            }
        }

        // Nothing found
        return null;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $model
     * @param \BetaKiller\Url\UrlPathIterator                 $it
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParameters
     *
     * @return bool
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\Behaviour\UrlBehaviourException
     */
    private function processUrlBehaviour(
        UrlElementInterface $model,
        UrlPathIterator $it,
        UrlContainerInterface $urlParameters
    ): bool {
        $behaviour = $this->behaviourFactory->fromUrlElement($model);

        return $behaviour->parseUri($model, $it, $urlParameters);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $urlElement
     * @param \BetaKiller\Url\UrlElementStack                 $stack
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParameters
     *
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    private function pushToStack(
        UrlElementInterface $urlElement,
        UrlElementStack $stack,
        UrlContainerInterface $urlParameters
    ): void {
        $this->checkUrlElementAccess($urlElement, $urlParameters);
        $stack->push($urlElement);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface             $urlElement
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParameters
     *
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \Spotman\Acl\Exception
     */
    private function checkUrlElementAccess(UrlElementInterface $urlElement, UrlContainerInterface $urlParameters): void
    {
        // Force authorization for non-public zones before security check
        $this->aclHelper->forceAuthorizationIfNeeded($urlElement);

        if (!$this->aclHelper->isUrlElementAllowed($urlElement, $urlParameters)) {
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
        $stackData  = $this->urlElementStack->getCodenames();
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
        ]), self::CACHE_TTL);

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

        if (!$serializedData) {
            return false;
        }

        $data = unserialize($serializedData, [
            UrlElementInterface::class,
            DispatchableEntityInterface::class,
        ]);

        if (!$data || !\is_array($data)) {
            // Log and keep processing as no cache was found
            $this->logger->warning('Cached UrlDispatcher data is incorrect', ['cachedData' => print_r($data, true)]);

            return false;
        }

        /** @var array $stackData */
        $stackData = $data['stack'];

        /** @var \BetaKiller\Url\Parameter\UrlParameterInterface[] $paramsData */
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
                $elementModel = $this->tree->getByCodename($ifaceCodename);
                $this->pushToStack($elementModel, $this->urlElementStack, $this->urlParameters);
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
