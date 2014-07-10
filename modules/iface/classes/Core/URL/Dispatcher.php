<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Core_URL_Dispatcher {

    use Util_Singleton;

    const PROTOTYPE_PCRE = '({([A-Za-z_]+)\.([A-Za-z_]+)})';

    protected $_url_parameters;

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

    public function parse_uri($prototype_string, $uri_value)
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

    public function make_uri($prototype_string, URL_Parameters $parameters = NULL)
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