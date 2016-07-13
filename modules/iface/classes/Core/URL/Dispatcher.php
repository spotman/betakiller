<?php

use BetaKiller\IFace\Core\IFace;

abstract class Core_URL_Dispatcher {

    use \BetaKiller\Utils\Instance\Singleton;

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
     * @return URL_Parameters
     */
    public function parameters()
    {
        if ( ! $this->_url_parameters )
        {
            $this->_url_parameters = URL_Parameters::instance();
        }

        return $this->_url_parameters;
    }

    public function iface_provider()
    {
        if ( ! $this->_iface_provider )
        {
            $this->_iface_provider = IFace_Provider::instance();
        }

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
        $uri_parts = $uri ? explode('/', $uri) : NULL;

        // Root requested - search for default IFace
        if ( ! $uri_parts )
        {
            $default_iface = $this->iface_provider()->get_default();
            $this->push_to_stack($default_iface);
            return $default_iface;
        }

        $parent_iface = NULL;
        $iface_instance = NULL;

        // Dispatch childs
        // Loop through every uri part and initialize it`s iface
        foreach ( $uri_parts as $uri_part )
        {
            // Prevent XSS via URL
            $uri_part = HTML::chars(strip_tags($uri_part));

            try
            {
                $iface_instance = NULL;
                $iface_instance = $this->by_uri($uri_part, $parent_iface);
            }
            catch ( URL_Dispatcher_Exception $e )
            {
//                throw $e;
                // Do nothing
            }

            // Throw IFace_Exception_MissingURL so we can forward user to parent iface or custom 404 page
            if ( ! $iface_instance )
                throw new IFace_Exception_MissingURL($uri_part, $parent_iface);

            // Store link to parent IFace if exists
            if ( $parent_iface)
                $iface_instance->set_parent($parent_iface);

            $parent_iface = $iface_instance;

            $this->push_to_stack($iface_instance);
        }

        // Return last IFace
        return $iface_instance;
    }

    /**
     * Performs iface search by uri (and optional parent iface model)
     *
     * @param string $uri
     * @param IFace|NULL $parent_iface
     * @return IFace
     * @throws URL_Dispatcher_Exception
     */
    public function by_uri($uri, IFace $parent_iface = NULL)
    {
        $layer = $this->iface_provider()->get_models_layer($parent_iface);

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
            if ( $iface_model->get_uri() == $uri )
                return $this->iface_provider()->iface_factory($iface_model);
        }

        // Second iteration for dynamic urls
        if ( $dynamic_model )
        {
            $this->parse_uri_parameter_part($dynamic_model->get_uri(), $uri);
            return $this->iface_provider()->iface_factory($dynamic_model);
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
            $registry->set($model_name, $model);
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
