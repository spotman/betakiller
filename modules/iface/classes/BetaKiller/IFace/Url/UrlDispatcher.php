<?php
namespace BetaKiller\IFace\Url;

use BetaKiller\Helper\InProductionTrait;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\Exception\IFaceMissingUrlException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentInterface;
use Exception;
use HTTP;
use IFace_Provider;
use Model;

//use BetaKiller\IFace\HasCustomUrlBehaviour;

class UrlDispatcher
{
    use InProductionTrait;

    const PROTOTYPE_PCRE = '(\{([A-Za-z_]+)\.([A-Za-z_]+)(\(\))*\})';

    /**
     * @var UrlParameters
     */
    protected $_url_parameters;

    /**
     * @var IFace_Provider
     */
    protected $_iface_provider;

    /**
     * @var IFaceInterface[]
     */
    protected $_iface_stack = [];

    /**
     * @var IFaceInterface
     */
    protected $_current_iface;

    /**
     * @param UrlParameters  $_url_parameters
     * @param IFace_Provider $_iface_provider
     */
    public function __construct(UrlParameters $_url_parameters, IFace_Provider $_iface_provider)
    {
        $this->_url_parameters = $_url_parameters;
        $this->_iface_provider = $_iface_provider;
    }

    /**
     * @return \BetaKiller\IFace\Url\UrlParameters
     */
    public function parameters()
    {
        return $this->_url_parameters;
    }

    public function iface_provider()
    {
        return $this->_iface_provider;
    }

    /**
     * Performs parsing of requested url
     * Returns IFace
     *
     * @param $uri
     *
     * @return IFaceInterface|null
     * @throws IFaceMissingUrlException
     * @throws Exception
     */
    public function parse_uri($uri)
    {
        // Prevent XSS via URL
        $uri = htmlspecialchars(strip_tags($uri), ENT_QUOTES);

        // TODO Check stack cache and url params for current URL

        // Creating URL iterator
        $url_iterator = new UrlPathIterator($uri);

        $parent_iface   = null;
        $iface_instance = null;

        // Dispatch childs
        // Loop through every uri part and initialize it`s iface
        do {
            $iface_instance = null;

            try {
                $root_requested = !$url_iterator->count();

                if ($root_requested) {
                    $iface_instance = $this->iface_provider()->get_default();

                    $this->process_iface_url_behaviour($iface_instance->getModel(), $url_iterator);
                } else {
                    $iface_instance = $this->parse_uri_layer($url_iterator, $parent_iface);
                }
            } catch (UrlDispatcherException $e) {
                if (!$this->in_production(true))
                    throw $e;

                // Do nothing
            }

            // Throw IFaceMissingUrlException so we can forward user to parent iface or custom 404 page
            if (!$iface_instance) {
                $this->throw_missing_url_exception($url_iterator, $parent_iface);
            }

            // Store link to parent IFace if exists
            if ($parent_iface) {
                $iface_instance->setParent($parent_iface);
            }

            $parent_iface = $iface_instance;

            $this->push_to_stack($iface_instance);

            $url_iterator->next();
        } while ($url_iterator->valid());

        // TODO Cache stack + url parameters (between HTTP requests) for current URL

        // Return last IFace
        return $iface_instance;
    }

    protected function throw_missing_url_exception(UrlPathIterator $it, IFaceInterface $parent_iface = null)
    {
        throw new IFaceMissingUrlException($it->current(), $parent_iface);
    }

    /**
     * Performs iface search by uri part(s) in iface layer
     *
     * @param UrlPathIterator     $it
     * @param IFaceInterface|NULL $parent_iface
     *
     * @return IFaceInterface|null
     * @throws UrlDispatcherException
     * @throws IFaceException
     */
    protected function parse_uri_layer(UrlPathIterator $it, IFaceInterface $parent_iface = null)
    {
        $layer = [];

        try {
            $layer = $this->iface_provider()->get_models_layer($parent_iface);
        } catch (IFaceException $e) {
            if (!$this->in_production(true))
                throw $e;

            $parent_url = $parent_iface ? $parent_iface->url($this->parameters(), false) : null;

            if ($parent_url) {
                // TODO PSR-7 Create interface for redirect() method, use it in Response and send Response instance to $this via DI
                HTTP::redirect($parent_url);
            } else {
                $this->throw_missing_url_exception($it, $parent_iface);
            }
        }

        $model = $this->select_iface_model($layer, $it);

        return $model ? $this->iface_from_model_factory($model) : null;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface[] $models
     *
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     * @throws IFaceException
     */
    protected function sort_models_layer(array $models)
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

        if (count($dynamic) > 1)
            throw new IFaceException('Layer must have only one IFace with dynamic dispatching');

        // Fixed URLs first, dynamic URLs last
        return array_merge($fixed, $dynamic);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface[] $models
     * @param UrlPathIterator                         $it
     *
     * @return \BetaKiller\IFace\IFaceModelInterface|NULL
     */
    protected function select_iface_model(array $models, UrlPathIterator $it)
    {
        // Put fixed urls first
        $models = $this->sort_models_layer($models);

        foreach ($models as $model) {
            if ($this->process_iface_url_behaviour($model, $it)) {
                return $model;
            }
        }

        // Nothing found
        return null;
    }

    public function process_iface_url_behaviour(IFaceModelInterface $model, UrlPathIterator $it)
    {
        $model_uri = $model->getUri();

        if ($it->count() && $model->hasTreeBehaviour()) {
            // Tree behaviour in URL
            $absent_found = false;
            $step         = 1;

            do {
                try {
                    $this->parse_uri_parameter_part($model_uri, $it->current());
                    $it->next();
                    $step++;
                } catch (UrlDispatcherException $e) {
                    $absent_found = true;

                    // Move one step back so current uri part will be processed by the next iface
                    $it->prev();
                }
            } while (!$absent_found AND $it->valid());

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
            $this->parse_uri_parameter_part($model_uri, $it->current());

            return true;
        } elseif ($model_uri == $it->current()) {
            // Fixed URL found, simply exit
            return true;
        }

        // No processing done
        return false;
    }

    public function get_iface_model_available_urls(IFaceModelInterface $model, UrlParameters $params, $limit = null, $with_domain = true)
    {
        if ($model->hasDynamicUrl()) {
            return $this->get_dynamic_model_available_urls($model, $params, $limit, $with_domain);
        } else {
            // Make static URL
            $iface = $this->iface_from_model_factory($model);

            return [$this->get_iface_url($iface, $params, $with_domain)];
        }
    }

    public function reset()
    {
        $this->_url_parameters->clear();
        $this->_iface_stack   = [];
        $this->_current_iface = null;
    }

    protected function get_dynamic_model_available_urls(IFaceModelInterface $iface_model, UrlParameters $params, $limit = null, $with_domain = true)
    {
        $urls  = [];
        $iface = $this->iface_from_model_factory($iface_model);

        $prototype = UrlPrototype::instance()->parse($iface_model->getUri());

        $model_name = $prototype->getModelName();
        $model_key  = $prototype->getModelKey();

        $model = $this->model_factory($model_name);

        $items = $model->get_available_items_by_url_key($model_key, $params, $limit);

        foreach ($items as $item) {
            // Save current item to parameters registry
            $params->set($model_name, $item, true);

            // Make dynamic URL + recursion
            $urls[] = $this->get_iface_url($iface, $params, $with_domain);

            if ($iface_model->hasTreeBehaviour()) {
                // Recursion for tree behaviour
                $urls = array_merge($urls, $this->get_dynamic_model_available_urls($iface_model, $params, $limit));
            }
        }

        return $urls;
    }

    protected function get_iface_url(IFaceInterface $iface, UrlParameters $params = null, $with_domain = true)
    {
        return $iface->url($params, false, $with_domain);
    }

//    protected function processCustomUrlBehaviour(IFaceModelInterface $iface_model, UrlPathIterator $it)
//    {
//        // Create instance of starter IFace
//        $starter_iface = $this->iface_from_model_factory($iface_model);
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

    protected function iface_from_model_factory(IFaceModelInterface $model)
    {
        return $this->iface_provider()->from_model($model);
    }

    /**
     * Returns TRUE if provided IFace was initialized through url parsing
     *
     * @param IFaceInterface $iface
     *
     * @return bool
     */
    public function in_stack(IFaceInterface $iface)
    {
        return isset($this->_iface_stack[$iface->getCodename()]);
    }

    /**
     * Returns COPY of the IFace stack
     * @return IFaceInterface[]
     */
    public function stack()
    {
        return array_values($this->_iface_stack);
    }

    /**
     * @return IFaceInterface
     */
    public function currentIFace()
    {
        return $this->_current_iface;
    }

    /**
     * @param IFaceInterface     $iface
     * @param UrlParameters|NULL $parameters
     *
     * @return bool
     */
    public function is_current_iface(IFaceInterface $iface, UrlParameters $parameters = null)
    {
        if (!$this->_current_iface || $this->_current_iface->getCodename() != $iface->getCodename()) {
            return false;
        }

        if (!$parameters) {
            return true;
        }

        $current_params = $this->parameters();

        foreach ($parameters->getAll() as $key => $param_model) {
            /** @var UrlDataSourceInterface $param_model */

            if (!$current_params->has($key)) {
                return false;
            }

            /** @var UrlDataSourceInterface $current_model */
            $current_model = $current_params->get($key);

            if ($param_model->get_url_item_id() != $current_model->get_url_item_id()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param IFaceInterface $iface
     *
     * @return $this
     */
    protected function push_to_stack(IFaceInterface $iface)
    {
        $this->_iface_stack[$iface->getCodename()] = $iface;
        $this->_current_iface                      = $iface;

        return $this;
    }

    public function replace_url_parameters_parts($source_uri_string, UrlParameters $parameters = null)
    {
        return preg_replace_callback(
            self::PROTOTYPE_PCRE,
            function ($matches) use ($parameters) {
                return $this->make_url_parameter_part($matches[0], $parameters);
            },
            $source_uri_string
        );
    }

    public function parse_url_parameters_parts($source_string)
    {
        preg_match_all(self::PROTOTYPE_PCRE, $source_string, $matches, PREG_SET_ORDER);

        // TODO Подготовить регулярку, которая выловит значения ключей из $source_string
        // Сделать это через замену всех прототипов ключей на регулярку (\S+) + экранирование остальных символов, не входящих в прототип

//        foreach ( $matches as $match )
//        {
//        }
    }

    public function parse_uri_parameter_part($prototype_string, $uri_value)
    {
        $prototype = $this->parse_prototype($prototype_string);

        $model_name = $prototype->getModelName();
        $model_key  = $prototype->getModelKey();

        $dataSource = $this->model_factory($model_name);

        if (!$uri_value) {
            // Allow processing of root element
            $uri_value = $dataSource->get_default_url_value();
        }

        // Search for model item
        $model = $dataSource->find_by_url_key($model_key, $uri_value, $this->parameters());

        if (!$model)
            throw new UrlDispatcherException('Can not find [:prototype] item by [:value]',
                [':prototype' => $prototype_string, ':value' => $uri_value]
            );

        // Allow current model to preset "belongs to" models
        $model->preset_linked_models($this->parameters());

        // Store model into registry
        $setter   = mb_strtolower('set_'.$model_name);
        $registry = $this->parameters();

        if (method_exists($registry, $setter)) {
            $registry->$setter($model);
        } else {
            $registry->set($model_name, $model, true); // Allow tree url behaviour to set value multiple times
        }
    }

    public function make_iface_uri(IFaceInterface $iface, UrlParameters $parameters = null)
    {
        $uri = $iface->getUri();

        if (!$uri)
            throw new IFaceException('IFace :codename must have uri');

        $model = $iface->getModel();

        if ($model->hasDynamicUrl()) {
            return $this->make_url_parameter_part($uri, $parameters, $model->hasTreeBehaviour());
        } else {
            return $uri;
        }
    }

    protected function make_url_parameter_part($prototype_string, UrlParameters $parameters = null, $is_tree = false)
    {
        $prototype = $this->parse_prototype($prototype_string);

        $model_name = $prototype->getModelName();
        $model_key  = $prototype->getModelKey();

        /** @var UrlDataSourceInterface $model */
        $model = $parameters ? $parameters->get($model_name) : null;

        // Inherit model from current request url parameters
        $model = $model ?: $this->parameters()->get($model_name);

        if (!$model)
            throw new UrlDispatcherException('Can not find :name model in parameters', [':name' => $model_name]);

        if ($is_tree AND !($model instanceof TreeModelSingleParentInterface))
            throw new UrlDispatcherException('Model :model must be instance of :object for tree traversing', [
                ':model'  => get_class($model),
                ':object' => TreeModelSingleParentInterface::class,
            ]);

        $parts = [];

        do {
            $parts[] = $this->calculate_model_key_value($model, $model_key, $prototype->isMethodCall());
        } while ($is_tree AND ($model = $model->get_parent()));

        return implode('/', array_reverse($parts));
    }

    protected function calculate_model_key_value(UrlDataSourceInterface $model, $key, $is_method_call)
    {
        if ($is_method_call) {
            $method = $key;

            if (!method_exists($model, $method))
                throw new UrlDispatcherException('Method :method does not exists in model :model',
                    [':method' => $method, ':model' => get_class($model)]);

            return $model->$method();
        } else {
            // Get url prototype value
            return $model->get_url_key_value($key);
        }
    }

    protected function parse_prototype($prototype)
    {
        return UrlPrototype::instance()->parse($prototype);
    }

    /**
     * @param $model_name
     *
     * @return UrlDataSourceInterface
     * @throws UrlDispatcherException
     */
    public function model_factory($model_name)
    {
        /** @var UrlDataSourceInterface $object */
        $object = Model::factory($model_name);

        if (!($object instanceof UrlDataSourceInterface))
            throw new UrlDispatcherException('The model :name must implement :proto', [
                ':name'  => $model_name,
                ':proto' => UrlDataSourceInterface::class,
            ]);

        return $object;
    }
}
