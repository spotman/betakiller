<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\Event\UrlDispatchedEvent;
use BetaKiller\Helper\AclHelper;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\Exception\IFaceMissingUrlException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceProvider;
use BetaKiller\IFace\IFaceStack;
use BetaKiller\MessageBus\EventBus;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Repository\RepositoryException;
use HTTP;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

//use BetaKiller\IFace\HasCustomUrlBehaviourInterface;

class UrlDispatcher implements LoggerAwareInterface
{
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
     * @var \BetaKiller\IFace\Url\UrlContainerInterface
     */
    private $urlParameters;

    /**
     * @var IFaceProvider
     */
    private $ifaceProvider;

    /**
     * @var \BetaKiller\IFace\Url\UrlPrototypeHelper
     */
    private $urlPrototypeHelper;

    /**
     * @var \BetaKiller\IFace\Url\UrlDispatcherCacheInterface
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
     * @param \BetaKiller\IFace\IFaceStack                      $stack
     * @param \BetaKiller\IFace\IFaceProvider                   $provider
     * @param \BetaKiller\IFace\Url\UrlContainerInterface       $parameters
     * @param \BetaKiller\IFace\Url\UrlPrototypeHelper          $prototypeHelper
     * @param \BetaKiller\IFace\Url\UrlDispatcherCacheInterface $cache
     * @param \BetaKiller\MessageBus\EventBus                   $eventBus
     * @param \BetaKiller\Helper\AclHelper                      $aclHelper
     */
    public function __construct(
        IFaceStack $stack,
        IFaceProvider $provider,
        UrlContainerInterface $parameters,
        UrlPrototypeHelper $prototypeHelper,
        UrlDispatcherCacheInterface $cache,
        EventBus $eventBus,
        AclHelper $aclHelper
    ) {
        $this->ifaceStack         = $stack;
        $this->ifaceProvider      = $provider;
        $this->urlParameters      = $parameters;
        $this->urlPrototypeHelper = $prototypeHelper;
        $this->cache              = $cache;
        $this->eventBus           = $eventBus;
        $this->aclHelper          = $aclHelper;
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
     * @throws \BetaKiller\IFace\Exception\IFaceException
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

                    $this->processIFaceUrlBehaviour($ifaceInstance->getModel(), $urlIterator);
                } else {
                    $ifaceInstance = $this->parseUriLayer($urlIterator, $parentIFace);
                }
            } catch (UrlDispatcherException $e) {
                // Log this exception and keep processing
                $this->logger->alert('Url parsing error', ['exception' => $e]);
            }

            // Throw IFaceMissingUrlException so we can forward user to parent iface or custom 404 page
            if (!$ifaceInstance) {
                $this->throwMissingUrlException($urlIterator, $parentIFace);
            }

            // Store link to parent IFace if exists
            if ($parentIFace) {
                $ifaceInstance->setParent($parentIFace);
            }

            $parentIFace = $ifaceInstance;

            $this->pushToStack($ifaceInstance);

            $urlIterator->next();
        } while ($urlIterator->valid());
    }

    protected function throwMissingUrlException(UrlPathIterator $it, IFaceInterface $parentIFace = null): void
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
     * @throws IFaceException
     */
    protected function parseUriLayer(UrlPathIterator $it, IFaceInterface $parentIFace = null): ?IFaceInterface
    {
        $layer = [];

        try {
            $layer = $this->ifaceProvider->getModelsLayer($parentIFace);
        } catch (IFaceException $e) {
            // Log this exception and keep processing
            $this->logger->warning('Url parsing error', ['exception' => $e]);

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
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function selectIFaceModel(array $models, UrlPathIterator $it): ?IFaceModelInterface
    {
        // Put fixed urls first
        $models = $this->sortModelsLayer($models);

        foreach ($models as $model) {
            if ($this->processIFaceUrlBehaviour($model, $it)) {
                return $model;
            }
        }

        // Nothing found
        return null;
    }

    private function processIFaceUrlBehaviour(IFaceModelInterface $model, UrlPathIterator $it): bool
    {
        if ($it->count() && $model->hasTreeBehaviour()) {
            // Tree behaviour in URL
            $absentFound = false;

            do {
                try {
                    $this->parseUriParameterPart($model, $it);
                    $it->next();
                } /** @noinspection BadExceptionsProcessingInspection */ catch (UrlDispatcherException $e) {
                    $absentFound = true;

                    // Move one step back so current uri part will be processed by the next iface
                    $it->prev();
                }
            } while (!$absentFound && $it->valid());

            // End tree behaviour

            return true;
        }
//        elseif ($model->has_custom_url_behaviour())
//        {
//            $this->processCustomUrlBehaviour($model, $it);
//            return $model;
//        }

        if ($model->hasDynamicUrl()) {
            // Regular dynamic URL, parse uri
            $this->parseUriParameterPart($model, $it);

            return true;
        }

        if ($model->getUri() === $it->current()) {
            // Fixed URL found, simply exit
            return true;
        }

        // No processing done
        return false;
    }

//    protected function processCustomUrlBehaviour(IFaceModelInterface $iface_model, UrlPathIterator $it)
//    {
//        // Create instance of starter IFace
//        $starter_iface = $this->createIFaceFromModel($iface_model);
//
//        // Check instance implements interface
//        if (!($starter_iface  instanceof HasCustomUrlBehaviourInterface))
//            throw new UrlDispatcherException('IFace :codename must implement :base', [
//                ':codename' =>  $starter_iface->getCodename(),
//                ':base'     =>  HasCustomUrlBehaviourInterface::class,
//            ]);
//
//        // Getting custom behaviour instance
//        $behaviour = $starter_iface->getCustomUrlBehaviour();
//
//        // Pairs "iface codename" => "uri scheme"
//        $behaviour->processCustomUrlBehaviour($it, $this->parameters());
//    }

    /**
     * @param IFaceInterface $iface
     */
    private function pushToStack(IFaceInterface $iface): void
    {
        $this->checkIFaceAccess($iface);
        $this->ifaceStack->push($iface);
    }

//    public function parse_url_parameters_parts($source_string)
//    {
//        preg_match_all(self::PROTOTYPE_PCRE, $source_string, $matches, PREG_SET_ORDER);
//
//        // TODO Подготовить регулярку, которая выловит значения ключей из $source_string
//        // Сделать это через замену всех прототипов ключей на регулярку (\S+) + экранирование остальных символов, не входящих в прототип
//
//        foreach ( $matches as $match )
//        {
//        }
//    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $ifaceModel
     * @param \BetaKiller\IFace\Url\UrlPathIterator $it
     *
     * @throws \BetaKiller\IFace\Exception\IFaceException
     * @throws \BetaKiller\IFace\Url\UrlDispatcherException
     * @throws \BetaKiller\IFace\Url\UrlPrototypeException
     */
    private function parseUriParameterPart(IFaceModelInterface $ifaceModel, UrlPathIterator $it): void
    {
        $prototype  = $this->urlPrototypeHelper->fromIFaceModelUri($ifaceModel);
        $dataSource = $this->urlPrototypeHelper->getDataSourceInstance($prototype);

        $this->urlPrototypeHelper->validatePrototypeModelKey($prototype, $dataSource);

        // Root element have default uri
        $uriValue = ($it->rootRequested() || ($ifaceModel->isDefault() && !$ifaceModel->hasDynamicUrl()))
            ? self::DEFAULT_URI
            : $it->current();

        try {
            // Search for model item
            $item = $prototype->hasIdKey()
                ? $dataSource->findById((int)$uriValue)
                : $dataSource->findItemByUrlKeyValue($uriValue, $this->urlParameters);
        } catch (RepositoryException $e) {
            throw new UrlDispatcherException(':error', [':error' => $e->getMessage()], $e->getCode(), $e);
        }

        if ($item instanceof DispatchableEntityInterface) {
            // Allow current model to preset "belongs to" models
            $item->presetLinkedEntities($this->urlParameters);
        }

        // Store model into registry
        $registry = $this->urlParameters;

        if (method_exists($registry, mb_strtolower('set_'.$prototype->getModelKey()))) {
            throw new UrlDispatcherException('Method calls on UrlContainer registry are deprecated');
        }

        // Allow tree url behaviour to set value multiple times
        $registry->setParameter($item, $ifaceModel->hasTreeBehaviour());
    }

    private function checkIFaceAccess(IFaceInterface $iface): void
    {
        // Force authorization for non-public zones before security check
        $this->aclHelper->forceAuthorizationIfNeeded($iface);

        if (!$this->aclHelper->isIFaceAllowed($iface, $this->urlParameters)) {
            throw new \HTTP_Exception_403();
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

        /** @var \BetaKiller\IFace\Url\UrlParameterInterface[] $paramsData */
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
            $this->logger->warning('Error on unpacking UrlDispatcher data: ', ['exception' => $e]);
        }

        return false;
    }

    private function isParameterSerializable(UrlParameterInterface $param): bool
    {
        // TODO Deal with this (remove or refactor to using some kind of interface)
        return true;
    }

    private function getUrlCacheKey(string $url): string
    {
        return 'urlDispatcher.'.$url;
    }
}
