<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Core_URL_Dispatcher {

    use Util_Singleton;

    const PROTOTYPE_PCRE = '({([A-Za-z_]+)\.([A-Za-z_]+)})';

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
            $this->_url_parameters = URL_Parameters::factory();
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

            $iface_instance = $this->by_uri($uri_part, $parent_iface);

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
     * @throws IFace_Exception
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
                    throw new IFace_Exception('Layer must have only one IFace with dynamic dispatching');

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
            URL_Dispatcher::instance()->parse_dynamic_uri_part($dynamic_model->get_uri(), $uri);
            return $this->iface_provider()->iface_factory($dynamic_model);
        }

        // Nothing found
        return NULL;
    }

    /**
     * Returns TRUE if provided IFace was initialized through url parsing
     *
     * @param Core_IFace $iface
     * @return bool
     */
    public function in_stack(Core_IFace $iface)
    {
        return isset($this->_iface_stack[ $iface->get_codename() ]);
    }

    public function stack()
    {
        return $this->_iface_stack;
    }

    public function current_iface()
    {
        return $this->_current_iface;
    }

    protected function push_to_stack(IFace $iface)
    {
        $this->_iface_stack[ $iface->get_codename() ] = $iface;
        $this->_current_iface = $iface;
        return $this;
    }

    public function parse_dynamic_uri_part($prototype_string, $uri_value)
    {
        $prototype = $this->parse_prototype($prototype_string);

        $model_name = $prototype->get_model_name();
        $model_key = $prototype->get_model_key();

        // Search for model item
        $model = $this->model_factory($model_name)->find_by_url_key($model_key, $uri_value, $this->parameters());

        if ( ! $model )
            throw new Kohana_Exception('Can not find [:prototype] item by [:value]',
                array(':prototype' => $prototype_string, ':value' => $uri_value)
            );

        // Store model into registry
        $this->parameters()->set($model_name, $model);
    }

    public function make_dynamic_uri_part($prototype_string, URL_Parameters $parameters = NULL)
    {
        $prototype = $this->parse_prototype($prototype_string);

        $model_name = $prototype->get_model_name();
        $model_key = $prototype->get_model_key();

        /** @var URL_DataSource $model */
        $model = $parameters ? $parameters->get($model_name) : NULL;

        // Inherit model from current request url parameters
        $model = $model ?: $this->parameters()->get($model_name);

        if ( ! $model )
            throw new Kohana_Exception('Can not find :name model in parameters', array(':name' => $model_name));

        // Get url prototype value
        return $model->get_url_key_value($model_key);
    }

    protected function parse_prototype($prototype)
    {
        return URL_Prototype::factory()->parse($prototype);
    }

    /**
     * @param $model_name
     * @return URL_DataSource
     * @throws Kohana_Exception
     */
    protected function model_factory($model_name)
    {
        /** @var URL_DataSource $object */
        $object = Model::factory($model_name);

        if ( ! ($object instanceof URL_DataSource) )
            throw new Kohana_Exception('The model :name must implement URL_DataSource', array(':name' => $model_name));

        return $object;
    }

}
