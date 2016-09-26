<?php

use BetaKiller\IFace\Core\IFace;

class IFace_Provider {

    protected $_iface_instances;

    /**
     * @var IFace_Model_Provider
     */
    protected $_model_provider;

    /**
     * IFace_Provider constructor
     *
     * @param IFace_Model_Provider $_model_provider
     */
    public function __construct(IFace_Model_Provider $_model_provider)
    {
        $this->_model_provider = $_model_provider;
    }

    public function by_codename($codename)
    {
        $iface = $this->get_cache($codename);

        if ( ! $iface )
        {
            $model = $this->model_provider()->by_codename($codename);
            $iface = $this->iface_factory($model);

            $this->set_cache($codename, $iface);
        }

        return $iface;
    }

    public function from_model(IFace_Model $model)
    {
        $codename = $model->get_codename();

        $iface = $this->get_cache($codename);

        if ( ! $iface )
        {
            $iface = $this->iface_factory($model);

            $this->set_cache($codename, $iface);
        }

        return $iface;
    }

    protected function get_cache($codename)
    {
        return isset($this->_iface_instances[$codename])
            ? $this->_iface_instances[$codename]
            : NULL;
    }

    protected function set_cache($codename, IFace $iface)
    {
        $this->_iface_instances[ $codename ] = $iface;
    }

    /**
     * @param IFace $parent_iface
     * @return IFace_Model[]
     * @throws IFace_Exception
     */
    public function get_models_layer(IFace $parent_iface = NULL)
    {
        $parent_iface_model = $parent_iface ? $parent_iface->get_model() : NULL;

        $layer = $this->model_provider()->get_layer($parent_iface_model);

        if ( ! $layer )
            throw new IFace_Exception('Empty layer for :codename IFace',
                array(':codename' => $parent_iface->get_codename())
            );

        return $layer;
    }

    public function get_default()
    {
        $default_model = $this->model_provider()->get_default();

        return $this->iface_factory($default_model);
    }

    protected function iface_factory(IFace_Model $model)
    {
        $codename = $model->get_codename();

        $class_name = 'IFace_'.$codename;

        if ( ! class_exists($class_name) )
        {
            $class_name = 'IFace_Default';
        }

        $container = \BetaKiller\DI\Container::instance();

        /** @var IFace $object */
        $object = $container->get($class_name);

        if ( ! ($object instanceof IFace) )
            throw new IFace_Exception('Class :class must be instance of class IFace', array(':class' => $class_name));

        $object->set_model($model);

        return $object;
    }

    public function get_parent(IFace $iface)
    {
        $model = $iface->get_model();
        $parent_model = $this->model_provider()->get_parent($model);

        return $parent_model
            ? $this->from_model($parent_model)
            : NULL;
    }

    /**
     * @return IFace_Model_Provider
     */
    protected function model_provider()
    {
        return $this->_model_provider;
    }

}
