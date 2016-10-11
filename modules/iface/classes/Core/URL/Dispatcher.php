<?php

use BetaKiller\IFace\Core\IFace;
use BetaKiller\IFace\UrlPathIterator;

abstract class Core_URL_Dispatcher {

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

        // TODO Check stack cache for current URL


        // Creating URL iterator
        $url_iterator = new UrlPathIterator($uri);

        // Root requested - search for default IFace
        if ( ! $url_iterator->count() )
        {
            $default_iface = $this->iface_provider()->get_default();
            $this->push_to_stack($default_iface);
            return $default_iface;
        }

        $parent_iface = NULL;
        $iface_instance = NULL;

        // Dispatch childs
        // Loop through every uri part and initialize it`s iface
        while ($url_iterator->valid()) {

            try
            {
                $iface_instance = NULL;
                $iface_instance = $this->parse_uri_layer($url_iterator, $parent_iface);
            }
            catch ( URL_Dispatcher_Exception $e )
            {
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
        }

        // TODO Cache stack for current URL

        // Return last IFace
        return $iface_instance;
    }

    protected function throw_missing_url_exception(UrlPathIterator $it, IFace $parent_iface)
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
            $parent_url = $parent_iface->url($this->parameters());

            // TODO Create interface for redirect() method, use it in Response and send Response instance to $this via DI
            HTTP::redirect($parent_url);

//            $this->throw_missing_url_exception($it, $parent_iface);
        }

        $iface_model = NULL;
        $dynamic_model = NULL;

        // First iteration through static urls
        foreach ( $layer as $iface_model )
        {
            // Skip ifaces with dynamic urls
            if ( $iface_model->has_dynamic_url() )
            {
                if ( $dynamic_model )
                    throw new URL_Dispatcher_Exception('Layer must have only one IFace with dynamic dispatching');

                $dynamic_model = $iface_model;
                continue;
            }

            // IFace found by concrete uri
            if ( $iface_model->get_uri() == $it->current() )
                return $this->iface_provider()->from_model($iface_model);
        }

        // Second iteration for dynamic urls
        if ( $dynamic_model )
        {
            // Tree iface processing
            if ($iface_model->has_tree_behaviour()) {

                $absent_found = false;
                $step = 1;

                do
                {
                    try
                    {
                        $this->parse_uri_parameter_part($dynamic_model->get_uri(), $it->current());
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

            } else {
                $this->parse_uri_parameter_part($dynamic_model->get_uri(), $it->current());
            }

            return $this->iface_provider()->from_model($dynamic_model);
        }

        // Nothing found
        return NULL;
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
     * @return array
     */
    public function stack()
    {
        return array_values($this->_iface_stack);
    }

    public function current_iface()
    {
        return $this->_current_iface;
    }

    public function is_current_iface(IFace $iface)
    {
        return ( $this->_current_iface->get_codename() == $iface->get_codename() );
    }

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

        // Search for model item
        $model = $this->model_factory($model_name)->find_by_url_key($model_key, $uri_value, $this->parameters());

        if ( ! $model )
            throw new URL_Dispatcher_Exception('Can not find [:prototype] item by [:value]',
                array(':prototype' => $prototype_string, ':value' => $uri_value)
            );

        // Store model into registry
        $setter = mb_strtolower('set_'.$model_name);
        $registry = $this->parameters();

        if (method_exists($registry, $setter))
            $registry->$setter($model);
        else
            $registry->set($model_name, $model, true); // Allow tree url behaviour to set value multiple times
    }

    public function make_url_parameter_part($prototype_string, URL_Parameters $parameters = NULL)
    {
        $prototype = $this->parse_prototype($prototype_string);

        $model_name = $prototype->get_model_name();
        $model_key = $prototype->get_model_key();

        /** @var URL_DataSource $model */
        $model = $parameters ? $parameters->get($model_name) : NULL;

        // Inherit model from current request url parameters
        $model = $model ?: $this->parameters()->get($model_name);

        if ( ! $model )
            throw new URL_Dispatcher_Exception('Can not find :name model in parameters', array(':name' => $model_name));

        if( $prototype->is_method_call() )
        {
            $method = $model_key;

            if ( ! method_exists($model, $method) )
                throw new URL_Dispatcher_Exception('Method :method does not exists in model :model',
                    array(':method' => $method, ':model' => $model_name));

            return $model->$method();
        }
        else
        {
            // Get url prototype value
            return $model->get_url_key_value($model_key);
        }
    }

    protected function parse_prototype($prototype)
    {
        return URL_Prototype::instance()->parse($prototype);
    }

    /**
     * @param $model_name
     * @return URL_DataSource
     * @throws URL_Dispatcher_Exception
     */
    public function model_factory($model_name)
    {
        /** @var URL_DataSource $object */
        $object = Model::factory($model_name);

        if ( ! ($object instanceof URL_DataSource) )
            throw new URL_Dispatcher_Exception('The model :name must implement URL_DataSource', array(':name' => $model_name));

        return $object;
    }

}
