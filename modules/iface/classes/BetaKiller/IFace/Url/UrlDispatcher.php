<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\Helper\AclHelper;
use BetaKiller\Helper\AppEnv;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\Exception\IFaceMissingUrlException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceProvider;
use BetaKiller\IFace\IFaceStack;
use BetaKiller\Model\DispatchableEntityInterface;
use HTTP;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

//use BetaKiller\IFace\HasCustomUrlBehaviour;

class UrlDispatcher implements LoggerAwareInterface
{
    /**
     * Defines default uri for index element (this used if root IFace has dynamic url behaviour)
     */
    const DEFAULT_URI = 'index';

    /**
     * Current IFace stack
     *
     * @var \BetaKiller\IFace\IFaceStack
     */
    private $ifaceStack;

    /**
     * Current Url parameters
     *
     * @var \BetaKiller\IFace\Url\UrlParametersInterface
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
     * @var \BetaKiller\Helper\AppEnv
     */
    private $appEnv;

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
     * @param \BetaKiller\IFace\Url\UrlParametersInterface      $parameters
     * @param \BetaKiller\IFace\Url\UrlPrototypeHelper          $prototypeHelper
     * @param \BetaKiller\IFace\Url\UrlDispatcherCacheInterface $cache
     * @param \BetaKiller\Helper\AppEnv                         $env
     * @param \BetaKiller\Helper\AclHelper                      $aclHelper
     */
    public function __construct(
        IFaceStack $stack,
        IFaceProvider $provider,
        UrlParametersInterface $parameters,
        UrlPrototypeHelper $prototypeHelper,
        UrlDispatcherCacheInterface $cache,
        AppEnv $env,
        AclHelper $aclHelper
    )
    {
        $this->ifaceStack         = $stack;
        $this->ifaceProvider      = $provider;
        $this->urlParameters      = $parameters;
        $this->urlPrototypeHelper = $prototypeHelper;
        $this->cache              = $cache;
        $this->appEnv             = $env;
        $this->aclHelper          = $aclHelper;
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process($uri)
    {
        // Prevent XSS via URL
        $uri = htmlspecialchars(strip_tags($uri), ENT_QUOTES);

        // Check cache for stack and url params for current URL
        if (!$this->restoreDataFromCache($uri)) {
            $this->parseUri($uri);

            // Cache stack + url parameters (between HTTP requests) for current URL
            $this->storeDataInCache($uri);
        }

        // Return last IFace
        return $this->ifaceStack->getCurrent();
    }

    /**
     * Performs parsing of requested url
     * Returns IFace
     *
     * @param string $uri
     *
     * @throws IFaceMissingUrlException
     */
    private function parseUri($uri)
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
                if (!$this->appEnv->inProduction(true)) {
                    throw $e;
                }

                // Log this exception and keep processing
                $this->logger->warning('Url parsing error', ['exception' => $e]);
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

    protected function throwMissingUrlException(UrlPathIterator $it, IFaceInterface $parentIFace = null)
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
     * @throws UrlDispatcherException
     * @throws IFaceException
     */
    protected function parseUriLayer(UrlPathIterator $it, IFaceInterface $parentIFace = null)
    {
        $layer = [];

        try {
            $layer = $this->ifaceProvider->getModelsLayer($parentIFace);
        } catch (IFaceException $e) {
            if (!$this->appEnv->inProduction(true)) {
                throw $e;
            }

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

    private function redirect($url, $code = null)
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
    protected function sortModelsLayer(array $models)
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

        if (count($dynamic) > 1) {
            throw new IFaceException('Layer must have only one IFace with dynamic dispatching');
        }

        // Fixed URLs first, dynamic URLs last
        return array_merge($fixed, $dynamic);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface[] $models
     * @param UrlPathIterator                         $it
     *
     * @return \BetaKiller\IFace\IFaceModelInterface|NULL
     */
    protected function selectIFaceModel(array $models, UrlPathIterator $it)
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

    public function processIFaceUrlBehaviour(IFaceModelInterface $model, UrlPathIterator $it)
    {
        if ($it->count() && $model->hasTreeBehaviour()) {
            // Tree behaviour in URL
            $absentFound = false;
            $step        = 1;

            do {
                try {
                    $this->parseUriParameterPart($model, $it);
                    $it->next();
                    $step++;
                } catch (UrlDispatcherException $e) {
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
//        if (!($starter_iface  instanceof HasCustomUrlBehaviour))
//            throw new UrlDispatcherException('IFace :codename must implement :base', [
//                ':codename' =>  $starter_iface->getCodename(),
//                ':base'     =>  HasCustomUrlBehaviour::class,
//            ]);
//
//        // Getting custom behaviour instance
//        $behaviour = $starter_iface->get_custom_url_behaviour();
//
//        // Pairs "iface codename" => "uri scheme"
//        $behaviour->processCustomUrlBehaviour($it, $this->parameters());
//    }

    /**
     * @param IFaceInterface $iface
     *
     * @return $this
     */
    protected function pushToStack(IFaceInterface $iface)
    {
        $this->checkIFaceAccess($iface);
        $this->ifaceStack->push($iface);

        return $this;
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

    public function parseUriParameterPart(IFaceModelInterface $ifaceModel, UrlPathIterator $it)
    {
        $prototype  = $this->urlPrototypeHelper->fromIFaceModelUri($ifaceModel);
        $dataSource = $this->urlPrototypeHelper->getDataSourceInstance($prototype);

        // Root element have default uri
        $uriValue = ($it->rootRequested() || ($ifaceModel->isDefault() && !$ifaceModel->hasDynamicUrl()))
            ? self::DEFAULT_URI
            : $it->current();

        $modelKey  = $prototype->getModelKey();
        $modelName = $prototype->getDataSourceName();

        $aclResource = $this->aclHelper->getAclResourceFromEntityName($modelName);

        // Search for model item
        $entity = $dataSource->findByUrlKey($modelKey, $uriValue, $this->urlParameters, $aclResource);

        if (!$entity) {
            throw new UrlDispatcherException('Can not find item for [:prototype] by [:value]', [
                ':prototype' => $ifaceModel->getUri(),
                ':value'     => $uriValue,
            ]);
        }

        $this->checkEntityAccess($entity);

        // Allow current model to preset "belongs to" models
        $entity->presetLinkedModels($this->urlParameters);

        // Store model into registry
        $setter   = mb_strtolower('set_'.$modelName);
        $registry = $this->urlParameters;

        if (method_exists($registry, $setter)) {
            throw new UrlDispatcherException('Method calls on UrlParameters registry are deprecated');
        }

        // Allow tree url behaviour to set value multiple times
        $registry->setEntity($entity, $ifaceModel->hasTreeBehaviour());
    }

    private function checkEntityAccess(DispatchableEntityInterface $entity)
    {
        if (!$this->aclHelper->isEntityActionAllowed($entity)) {
            throw new \HTTP_Exception_403();
        }
    }

    private function checkIFaceAccess(IFaceInterface $iface)
    {
        // Force authorization for non-public zones before security check
        $this->aclHelper->forceAuthorizationIfNeeded($iface);

        if (!$this->aclHelper->isIFaceAllowed($iface)) {
            throw new \HTTP_Exception_403();
        }
    }

    private function storeDataInCache($url)
    {
        $stackData  = $this->ifaceStack->getCodenames();
        $paramsData = $this->urlParameters->getAllEntities();

        $this->cache->set($url, [
            'stack'      => $stackData,
            'parameters' => $paramsData,
        ]);
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    private function restoreDataFromCache($url)
    {
        $data = $this->cache->get($url);

        if (!$data) {
            return false;
        }

        if (!is_array($data)) {
            // Log and keep processing as no cache was found
            $this->logger->warning('Cached UrlDispatcher data is incorrect', ['cachedData' => print_r($data, true)]);

            return false;
        }

        /** @var array $stackData */
        $stackData = $data['stack'];

        /** @var \BetaKiller\Model\DispatchableEntityInterface[] $paramsData */
        $paramsData = $data['parameters'];

        try {
            // Restore ifaces and push them into stack
            foreach ($stackData as $ifaceCodename) {
                $iface = $this->ifaceProvider->fromCodename($ifaceCodename);
                $this->ifaceStack->push($iface);
            }

            // Restore url parameters
            foreach ($paramsData as $key => $value) {
                if (!($value instanceof DispatchableEntityInterface)) {
                    throw new UrlDispatcherException('Cached data for url parameters is incorrect');
                }

                $this->checkEntityAccess($value);

                $this->urlParameters->setEntity($value);
            }

            return true;
        } catch (\Throwable $e) {
            // Log and keep processing as no cache was found
            $this->logger->warning('Error on unpacking UrlDispatcher data: ', ['exception' => $e]);
        } catch (\Exception $e) {
            // Log and keep processing as no cache was found
            $this->logger->warning('Error on unpacking UrlDispatcher data: ', ['exception' => $e]);
        }

        return false;
    }
}
