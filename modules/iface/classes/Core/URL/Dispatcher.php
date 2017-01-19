<?php

use BetaKiller\IFace\Core\IFace;
use BetaKiller\IFace\UrlPathIterator;
use BetaKiller\Utils\Kohana\TreeModelOrm;
//use BetaKiller\IFace\HasCustomUrlBehaviour;
use BetaKiller\IFace\IFaceModelInterface;

abstract class Core_URL_Dispatcher
{
    use \BetaKiller\Helper\InProduction;

//    use \BetaKiller\Utils\Instance\Simple;

    const PROTOTYPE_PCRE = '(\{([A-Za-z_]+)\.([A-Za-z_]+)(\(\))*\})';

    /**
     * @var URL_Parameters
     */
    protected $_url_parameters;

    /**
     * @var IFace_Provider
     */
    protected $_iface_provider;

    /**
     * @var array
     */
    protected $_iface_stack = [];

    /**
     * @var IFace
     */
    protected $_current_iface;

    /**
     * @param URL_Parameters $_url_parameters
     * @param IFace_Provider $_iface_provider
     */
    public function __construct(URL_Parameters $_url_parameters, IFace_Provider $_iface_provider)
    {
        $this->_url_parameters = $_url_parameters;
        $this->_iface_provider = $_iface_provider;
    }

    /**
     * @return \URL_Parameters
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
     * @return IFace|null
     * @throws IFace_Exception_MissingURL
     */
    public function parse_uri($uri)
    {
        // Prevent XSS via URL
        $uri = htmlspecialchars(strip_tags($uri), ENT_QUOTES);

        // TODO Check stack cache and url params for current URL


        // Creating URL iterator
        $url_iterator = new UrlPathIterator($uri);

        $parent_iface = NULL;
        $iface_instance = NULL;

        // Dispatch childs
        // Loop through every uri part and initialize it`s iface
        do {
            $iface_instance = NULL;

            try
            {
                $root_requested = !$url_iterator->count();

                if ($root_requested)
                {
                    $iface_instance = $this->iface_provider()->get_default();

                    $this->process_iface_url_behaviour($iface_instance->get_model(), $url_iterator);
                }
                else
                {
                    $iface_instance = $this->parse_uri_layer($url_iterator, $parent_iface);
                }
            }
            catch ( URL_Dispatcher_Exception $e )
            {
                if (!$this->in_production(TRUE))
                    throw $e;

                // Do nothing
            }

            // Throw IFace_Exception_MissingURL so we can forward user to parent iface or custom 404 page
            if ( ! $iface_instance ) {
                $this->throw_missing_url_exception($url_iterator, $parent_iface);
            }

            // Store link to parent IFace if exists
            if ( $parent_iface) {
                $iface_instance->set_parent($parent_iface);
            }

            $parent_iface = $iface_instance;

            $this->push_to_stack($iface_instance);

            $url_iterator->next();
        } while ($url_iterator->valid());

        // TODO Cache stack + url parameters (between HTTP requests) for current URL

        // Return last IFace
        return $iface_instance;
    }

    protected function throw_missing_url_exception(UrlPathIterator $it, IFace $parent_iface = null)
    {
        throw new IFace_Exception_MissingURL($it->current(), $parent_iface);
    }

    /**
     * Performs iface search by uri part(s) in iface layer
     *
     * @param UrlPathIterator $it
     * @param IFace|NULL $parent_iface
     * @return IFace|null
     * @throws URL_Dispatcher_Exception
     * @throws IFace_Exception
     */
    protected function parse_uri_layer(UrlPathIterator $it, IFace $parent_iface = null)
    {
        $layer = [];

        try
        {
            $layer = $this->iface_provider()->get_models_layer($parent_iface);
        }
        catch (IFace_Exception $e)
        {
            if (!$this->in_production(TRUE))
                throw $e;

            $parent_url = $parent_iface ? $parent_iface->url($this->parameters(), FALSE) : NULL;

            if ($parent_url) {
                // TODO PSR-7 Create interface for redirect() method, use it in Response and send Response instance to $this via DI
                HTTP::redirect($parent_url);
            } else {
                $this->throw_missing_url_exception($it, $parent_iface);
            }
        }

        $model = $this->select_iface_model($layer, $it);

        return $model ? $this->iface_from_model_factory($model) : NULL;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface[] $models
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     * @throws IFace_Exception
     */
    protected function sort_models_layer(array $models)
    {
        $fixed = [];
        $dynamic = [];

        foreach ($models as $model) {
            if ($model->has_dynamic_url() || $model->has_tree_behaviour()) {
                $dynamic[] = $model;
            } else {
                $fixed[] = $model;
            }
        }

        if ( count($dynamic) > 1 )
            throw new IFace_Exception('Layer must have only one IFace with dynamic dispatching');

        // Fixed URLs first, dynamic URLs last
        return array_merge($fixed, $dynamic);
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface[] $models
     * @param UrlPathIterator $it
     *
     * @return \BetaKiller\IFace\IFaceModelInterface|NULL
     */
    protected function select_iface_model(array $models, UrlPathIterator $it)
    {
        // Put fixed urls first
        $models = $this->sort_models_layer($models);

        foreach ($models as $model)
        {
            if ($this->process_iface_url_behaviour($model, $it))
            {
                return $model;
            }
        }

        // Nothing found
        return NULL;
    }

    public function process_iface_url_behaviour(IFaceModelInterface $model, UrlPathIterator $it)
    {
        $model_uri = $model->get_uri();

        if ($it->count() && $model->has_tree_behaviour())
        {
            // Tree behaviour in URL
            $absent_found = false;
            $step = 1;

            do
            {
                try
                {
                    $this->parse_uri_parameter_part($model_uri, $it->current());
                    $it->next();
                    $step++;
                }
                catch (URL_Dispatcher_Exception $e)
                {
                    $absent_found = true;

                    // Move one step back so current uri part will be processed by the next iface
                    $it->prev();
                }
            }
            while (!$absent_found AND $it->valid());
            // End tree behaviour

            return TRUE;
        }
//        elseif ($model->has_custom_url_behaviour())
//        {
//            $this->process_custom_url_behaviour($model, $it);
//            return $model;
//        }
        elseif ($model->has_dynamic_url())
        {
            // Regular dynamic URL, parse uri
            $this->parse_uri_parameter_part($model_uri, $it->current());
            return TRUE;
        }
        elseif ($model_uri == $it->current())
        {
            // Fixed URL found, simply exit
            return TRUE;
        }

        // No processing done
        return FALSE;
    }

    public function get_iface_model_available_urls(IFaceModelInterface $model, URL_Parameters $params, $limit = NULL, $with_domain = TRUE)
    {
        if ( $model->has_dynamic_url()  )
        {
            return $this->get_dynamic_model_available_urls($model, $params, $limit, $with_domain);
        }
        else
        {
            // Make static URL
            $iface = $this->iface_from_model_factory($model);
            return [$this->get_iface_url($iface, $params, $with_domain)];
        }
    }

    public function reset()
    {
        $this->_url_parameters->clear();
        $this->_iface_stack = [];
        $this->_current_iface = null;
    }

    protected function get_dynamic_model_available_urls(IFaceModelInterface $iface_model, URL_Parameters $params, $limit = NULL, $with_domain = TRUE)
    {
        $urls = [];
        $iface = $this->iface_from_model_factory($iface_model);

        $prototype = URL_Prototype::instance()->parse($iface_model->get_uri());

        $model_name = $prototype->get_model_name();
        $model_key = $prototype->get_model_key();

        $model = $this->model_factory($model_name);

        $items = $model->get_available_items_by_url_key($model_key, $params, $limit);

        foreach ($items as $item)
        {
            // Save current item to parameters registry
            $params->set($model_name, $item, TRUE);

            // Make dynamic URL + recursion
            $urls[] = $this->get_iface_url($iface, $params, $with_domain);

            if ($iface_model->has_tree_behaviour())
            {
                // Recursion for tree behaviour
                $urls = array_merge($urls, $this->get_dynamic_model_available_urls($iface_model, $params, $limit));
            }
        }

        return $urls;
    }

    protected function get_iface_url(IFace $iface, URL_Parameters $params = NULL, $with_domain = TRUE)
    {
        return $iface->url($params, FALSE, $with_domain);
    }

//    protected function process_custom_url_behaviour(IFaceModelInterface $iface_model, UrlPathIterator $it)
//    {
//        // Create instance of starter IFace
//        $starter_iface = $this->iface_from_model_factory($iface_model);
//
//        // Check instance implements interface
//        if (!($starter_iface  instanceof HasCustomUrlBehaviour))
//            throw new URL_Dispatcher_Exception('IFace :codename must implement :base', [
//                ':codename' =>  $starter_iface->get_codename(),
//                ':base'     =>  HasCustomUrlBehaviour::class,
//            ]);
//
//        // Getting custom behaviour instance
//        $behaviour = $starter_iface->get_custom_url_behaviour();
//
//        // Pairs "iface codename" => "uri scheme"
//        $behaviour->process_custom_url_behaviour($it, $this->parameters());
//    }

    protected function iface_from_model_factory(IFaceModelInterface $model)
    {
        return $this->iface_provider()->from_model($model);
    }

    /**
     * Returns TRUE if provided IFace was initialized through url parsing
     *
     * @param IFace $iface
     * @return bool
     */
    public function in_stack(IFace $iface)
    {
        return isset($this->_iface_stack[ $iface->get_codename() ]);
    }

    /**
     * Returns COPY of the IFace stack
     * @return IFace[]
     */
    public function stack()
    {
        return array_values($this->_iface_stack);
    }

    /**
     * @return IFace
     */
    public function current_iface()
    {
        return $this->_current_iface;
    }

    /**
     * @param IFace $iface
     * @param URL_Parameters|NULL $parameters
     * @return bool
     */
    public function is_current_iface(IFace $iface, URL_Parameters $parameters = NULL)
    {
        if (!$this->_current_iface || $this->_current_iface->get_codename() != $iface->get_codename()) {
            return FALSE;
        }

        if (!$parameters) {
            return TRUE;
        }

        $current_params = $this->parameters();

        foreach ( $parameters->getAll() as $key => $param_model) {   /** @var URL_DataSourceInterface $param_model */

            if ( !$current_params->has($key) ) {
                return FALSE;
            }

            /** @var URL_DataSourceInterface $current_model */
            $current_model = $current_params->get($key);

            if ($param_model->get_url_item_id() != $current_model->get_url_item_id()) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * @param IFace $iface
     * @return $this
     */
    protected function push_to_stack(IFace $iface)
    {
        $this->_iface_stack[ $iface->get_codename() ] = $iface;
        $this->_current_iface = $iface;
        return $this;
    }

    public function replace_url_parameters_parts($source_uri_string, URL_Parameters $parameters = NULL)
    {
        return preg_replace_callback(
            self::PROTOTYPE_PCRE,
            function($matches) use ($parameters)
            {
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

        $model_name = $prototype->get_model_name();
        $model_key = $prototype->get_model_key();

        $dataSource = $this->model_factory($model_name);

        if (!$uri_value) {
            // Allow processing of root element
            $uri_value = $dataSource->get_default_url_value();
        }

        // Search for model item
        $model = $dataSource->find_by_url_key($model_key, $uri_value, $this->parameters());

        if ( ! $model )
            throw new URL_Dispatcher_Exception('Can not find [:prototype] item by [:value]',
                array(':prototype' => $prototype_string, ':value' => $uri_value)
            );

        // Allow current model to preset "belongs to" models
        $model->preset_linked_models($this->parameters());

        // Store model into registry
        $setter = mb_strtolower('set_'.$model_name);
        $registry = $this->parameters();

        if (method_exists($registry, $setter))
        {
            $registry->$setter($model);
        }
        else
        {
            $registry->set($model_name, $model, true); // Allow tree url behaviour to set value multiple times
        }
    }

    public function make_iface_uri(IFace $iface, URL_Parameters $parameters = NULL)
    {
        $uri = $iface->get_uri();

        if (!$uri)
            throw new IFace_Exception('IFace :codename must have uri');

        $model = $iface->get_model();

        if ($model->has_dynamic_url()) {
            return $this->make_url_parameter_part($uri, $parameters, $model->has_tree_behaviour());
        } else {
            return $uri;
        }
    }

    protected function make_url_parameter_part($prototype_string, URL_Parameters $parameters = null, $is_tree = false)
    {
        $prototype = $this->parse_prototype($prototype_string);

        $model_name = $prototype->get_model_name();
        $model_key = $prototype->get_model_key();

        /** @var URL_DataSourceInterface $model */
        $model = $parameters ? $parameters->get($model_name) : NULL;

        // Inherit model from current request url parameters
        $model = $model ?: $this->parameters()->get($model_name);

        if ( ! $model )
            throw new URL_Dispatcher_Exception('Can not find :name model in parameters', array(':name' => $model_name));

        if ($is_tree AND !($model instanceof TreeModelOrm))
            throw new URL_Dispatcher_Exception('Model :model must be instance of :object for tree traversing', [
                ':model'    =>  get_class($model),
                ':object'   =>  TreeModelOrm::class,
            ]);

        $parts = [];

        do
        {
            $parts[] = $this->calculate_model_key_value($model, $model_key, $prototype->is_method_call());
        }
        while ($is_tree AND ($model = $model->get_parent()));

        return implode('/', array_reverse($parts));
    }

    protected function calculate_model_key_value(URL_DataSourceInterface $model, $key, $is_method_call)
    {
        if( $is_method_call )
        {
            $method = $key;

            if ( ! method_exists($model, $method) )
                throw new URL_Dispatcher_Exception('Method :method does not exists in model :model',
                    array(':method' => $method, ':model' => get_class($model)));

            return $model->$method();
        }
        else
        {
            // Get url prototype value
            return $model->get_url_key_value($key);
        }
    }

    protected function parse_prototype($prototype)
    {
        return URL_Prototype::instance()->parse($prototype);
    }

    /**
     * @param $model_name
     *
*@return URL_DataSourceInterface
     * @throws URL_Dispatcher_Exception
     */
    public function model_factory($model_name)
    {
        /** @var URL_DataSourceInterface $object */
        $object = Model::factory($model_name);

        if ( ! ($object instanceof URL_DataSourceInterface) )
            throw new URL_Dispatcher_Exception('The model :name must implement :proto', [
                ':name' => $model_name,
                ':proto' => URL_DataSourceInterface::class
            ]);

        return $object;
    }

}
