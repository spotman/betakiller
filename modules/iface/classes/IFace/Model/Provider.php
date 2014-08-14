<?php defined('SYSPATH') OR die('No direct script access.');

class IFace_Model_Provider implements IFace_Model_Provider_Interface {

    use Util_Singleton;

    /**
     * @var IFace_Model_Provider_DB[]|IFace_Model_Provider_Admin[]
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
        $model = NULL;

        foreach ( $this->get_sources() as $source )
        {
            if ( $model = $source->get_default() )
                break;
        }

        if ( ! $model )
            throw new IFace_Exception('No default IFace found');

        $this->set_cache($model);

        return $model;
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
        $model = $this->get_cache($codename);

        if ( ! $model )
        {
            foreach ( $this->get_sources() as $source )
            {
                if ( $model = $source->by_codename($codename) )
                    break;
            }

            if ( ! $model )
                throw new IFace_Exception('No IFace found by codename :codename', array(':codename' => $codename));

            $this->set_cache($model);
        }

        return $model;
    }

    /**
     * @param IFace_Model $parent_model
     * @return IFace_Model[]
     */
    public function get_layer(IFace_Model $parent_model = NULL)
    {
        return $parent_model
            ? $this->get_children($parent_model)
            : $this->get_root();
    }

    public function get_children(IFace_Model $parent_model = NULL)
    {
        $models = $parent_model->get_children();

        $this->cache_models($models);

        return $models;
    }

    /**
     * @param IFace_Model $model
     * @return IFace_Model|NULL
     */
    public function get_parent(IFace_Model $model)
    {
        $parent = $model->get_parent();

        if ( $parent )
            $this->set_cache($parent);

        return $parent;
    }

    /**
     * Returns list of root elements
     *
     * @return IFace_Model[]
     * @throws IFace_Exception
     */
    public function get_root()
    {
        $models = array();

        foreach ( $this->get_sources() as $source )
        {
            $models = array_merge($models, $source->get_root());
        }

        $this->cache_models($models);

        return $models;
    }

    protected function get_sources()
    {
        if ( ! $this->_sources )
        {
            $this->_sources = array(
                IFace_Model_Provider_DB::factory(),
                IFace_Model_Provider_Admin::factory(),
            );
        }

        return $this->_sources;
    }

//    protected function source_exec($method)
//    {
//        $value = NULL;
//
//        $call_args = func_get_args();
//        array_shift($call_args);
//
//        foreach ( $this->get_sources() as $source )
//        {
//            $value = call_user_func_array(array($source, $method), $call_args);
//
//            if ( $value )
//                break;
//        }
//
//        return $value;
//    }

    /**
     * @param IFace_Model[] $models
     */
    protected function cache_models(array $models)
    {
        foreach ( $models as $model )
        {
            $this->set_cache($model);
        }
    }

    /**
     * @param string $codename
     * @return IFace_Model|NULL
     */
    protected function get_cache($codename)
    {
        return isset($this->_model_instances[$codename])
            ? $this->_model_instances[$codename]
            : NULL;
    }

    protected function set_cache(IFace_Model $model)
    {
        $this->_model_instances[ $model->get_codename() ] = $model;
    }

}
