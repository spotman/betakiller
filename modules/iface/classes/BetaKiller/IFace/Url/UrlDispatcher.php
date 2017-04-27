<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\Helper\AppEnvTrait;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\Exception\IFaceMissingUrlException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\IFace\IFaceProvider;
use BetaKiller\IFace\IFaceStack;
use HTTP;

//use BetaKiller\IFace\HasCustomUrlBehaviour;

class UrlDispatcher
{
    use AppEnvTrait;

    /**
     * Current IFace stack
     *
     * @var \BetaKiller\IFace\IFaceStack
     */
    private $ifaceStack;

    /**
     * Current Url parameters
     *
     * @var UrlParameters
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
     * @param \BetaKiller\IFace\IFaceStack                      $stack
     * @param \BetaKiller\IFace\IFaceProvider                   $provider
     * @param \BetaKiller\IFace\Url\UrlParametersInterface      $parameters
     * @param \BetaKiller\IFace\Url\UrlPrototypeHelper          $helper
     * @param \BetaKiller\IFace\Url\UrlDispatcherCacheInterface $cache
     */
    public function __construct(
        IFaceStack $stack,
        IFaceProvider $provider,
        UrlParametersInterface $parameters,
        UrlPrototypeHelper $helper,
        UrlDispatcherCacheInterface $cache
    )
    {
        $this->ifaceStack         = $stack;
        $this->ifaceProvider      = $provider;
        $this->urlParameters      = $parameters;
        $this->urlPrototypeHelper = $helper;
        $this->cache              = $cache;
    }

    /**
     * @return \BetaKiller\IFace\Url\UrlParameters
     * @deprecated Use DI instead
     */
    public function parameters()
    {
        return $this->urlParameters;
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
                $rootRequested = !$urlIterator->count();

                if ($rootRequested) {
                    $ifaceInstance = $this->ifaceProvider->getDefault();

                    $this->processIFaceUrlBehaviour($ifaceInstance->getModel(), $urlIterator);
                } else {
                    $ifaceInstance = $this->parseUriLayer($urlIterator, $parentIFace);
                }
            } catch (UrlDispatcherException $e) {
                if (!$this->in_production(true)) {
                    throw $e;
                }

                // Do nothing
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
            if (!$this->in_production(true)) {
                throw $e;
            }

            $parentUrl = $parentIFace ? $parentIFace->url($this->urlParameters, false) : null;

            if ($parentUrl) {
                // TODO PSR-7 Create interface for redirect() method, use it in Response and send Response instance to $this via DI
                HTTP::redirect($parentUrl);
            } else {
                $this->throwMissingUrlException($it, $parentIFace);
            }
        }

        $model = $this->selectIFaceModel($layer, $it);

        return $model ? $this->ifaceProvider->fromModel($model) : null;
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
                    $this->parseUriParameterPart($model, $it->current());
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
        elseif ($model->hasDynamicUrl()) {
            // Regular dynamic URL, parse uri
            $this->parseUriParameterPart($model, $it->current());

            return true;
        } elseif ($model->getUri() === $it->current()) {
            // Fixed URL found, simply exit
            return true;
        }

        // No processing done
        return false;
    }

    /**
     * @deprecated Url dispatching must be persistent
     */
    public function reset()
    {
        $this->urlParameters->clear();
        $this->ifaceStack->clear();
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
     * Returns TRUE if provided IFace was initialized through url parsing
     *
     * @param IFaceInterface $iface
     *
     * @deprecated Use IFaceStack::has() instead
     *
     * @return bool
     */
    public function hasInStack(IFaceInterface $iface)
    {
        return $this->ifaceStack->has($iface);
    }

    /**
     * @return IFaceInterface|null
     */
    public function currentIFace()
    {
        return $this->ifaceStack->getCurrent();
    }

    /**
     * @param IFaceInterface                                    $iface
     * @param \BetaKiller\IFace\Url\UrlParametersInterface|NULL $parameters
     *
     * @return bool
     */
    public function isCurrentIFace(IFaceInterface $iface, UrlParametersInterface $parameters = null)
    {
        return $this->ifaceStack->isCurrent($iface, $parameters);
    }

    /**
     * @param IFaceInterface $iface
     *
     * @return $this
     */
    protected function pushToStack(IFaceInterface $iface)
    {
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

    public function parseUriParameterPart(IFaceModelInterface $ifaceModel, $uriValue)
    {
        $prototype  = $this->urlPrototypeHelper->fromIFaceModelUri($ifaceModel);
        $dataSource = $this->urlPrototypeHelper->getModelInstance($prototype);

        if (!$uriValue) {
            // Allow processing of root element
            $uriValue = $dataSource->getDefaultUrlValue();
        }

        $modelName = $prototype->getModelName();
        $modelKey  = $prototype->getModelKey();

        // Search for model item
        $model = $dataSource->findByUrlKey($modelKey, $uriValue, $this->urlParameters);

        if (!$model) {
            throw new UrlDispatcherException('Can not find item for [:prototype] by [:value]', [
                ':prototype' => $ifaceModel->getUri(),
                ':value'     => $uriValue,
            ]);
        }

        // Allow current model to preset "belongs to" models
        $model->presetLinkedModels($this->urlParameters);

        // Store model into registry
        $setter   = mb_strtolower('set_'.$modelName);
        $registry = $this->urlParameters;

        if (method_exists($registry, $setter)) {
            throw new UrlDispatcherException('Method calls on UrlParameters registry are deprecated');
        }

        // Allow tree url behaviour to set value multiple times
        $registry->set($modelName, $model, true);
    }

    private function storeDataInCache($url)
    {
        $stackData = $this->ifaceStack->getCodenames();
        $paramsData = $this->urlParameters->getAll();

        $this->cache->set($url, [
            'stack' => $stackData,
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
            throw new UrlDispatcherException('Cached data is incorrect');
        }

        /** @var array $stackData */
        $stackData = $data['stack'];

        /** @var \BetaKiller\IFace\Url\UrlDataSourceInterface[] $paramsData */
        $paramsData = $data['parameters'];

        // Restore ifaces and push them into stack
        foreach ($stackData as $ifaceCodename) {
            $iface = $this->ifaceProvider->fromCodename($ifaceCodename);
            $this->ifaceStack->push($iface);
        }

        // Restore url parameters
        foreach ($paramsData as $key => $value) {

            if (!($value instanceof UrlDataSourceInterface)) {
                throw new UrlDispatcherException('Cached data for url parameters is incorrect');
            }

            $this->urlParameters->set($key, $value);
        }

        return true;
    }
}
