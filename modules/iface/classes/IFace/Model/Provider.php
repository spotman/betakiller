<?php defined('SYSPATH') OR die('No direct script access.');

class IFace_Model_Provider implements IFace_Model_Provider_Interface {

    use Util_Singleton,
        Util_Factory_Cached;

    /**
     * @var IFace_Model_Provider[]
     */
    protected $_sources;

    /**
     * @var IFace_Model[]
     */
    protected $_model_instances = array();

    /**
     * Returns default iface model in current provider
     *
     * @return IFace_Model|null
     * @throws IFace_Exception
     */
    public function get_default()
    {
        $default_iface = $this->source_exec('get_default');

        if ( ! $default_iface )
            throw new IFace_Exception('No default IFace found');

        return $default_iface;
    }

    /**
     * Returns iface model by codename or NULL if none was found
     *
     * @param $codename
     * @return IFace_Model|null
     * @throws IFace_Exception
     */
    public function by_codename($codename)
    {
        // Caching models
        if ( ! isset($this->_model_instances[$codename]) )
        {
            $iface_model = $this->source_exec('by_codename', $codename);

            if ( ! $iface_model )
                throw new IFace_Exception('No IFace found by codename :codename', array(':codename' => $codename));

            $this->_model_instances[$codename] = $iface_model;
        }

        return $this->_model_instances[$codename];
    }

    /**
     * @param IFace_Model $parent_model
     * @return IFace_Model[]
     */
    public function get_layer(IFace_Model $parent_model = NULL)
    {
        return $parent_model
            ? $parent_model->get_children()
            : $this->get_root();
    }

    /**
     * Returns list of root elements
     *
     * @return IFace_Model[]
     */
    public function get_root()
    {
        $models = array();

        foreach ( $this->get_sources() as $source )
        {
            $models = array_merge($models, $source->get_root());
        }

        return $models;
    }

    protected function get_sources()
    {
        if ( ! $this->_sources )
        {
            // TODO get actual sources from config

            $this->_sources = array(
                IFace_Model_Provider::factory('DB'),
                IFace_Model_Provider::factory('Admin'),
            );
        }

        return $this->_sources;
    }

    protected function source_exec($method)
    {
        $value = NULL;

        $call_args = func_get_args();
        array_shift($call_args);

        foreach ( $this->get_sources() as $source )
        {
            $value = call_user_func_array(array($source, $method), $call_args);

            if ( $value )
                break;
        }

        return $value;
    }

}