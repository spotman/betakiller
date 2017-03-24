<?php

use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Config\AppConfigInterface;
use BetaKiller\DI\ContainerInterface;

class IFace_Provider
{
    /**
     * @var IFaceInterface[]
     */
    protected $_iface_instances;

    /**
     * @var IFace_Model_Provider
     */
    protected $_model_provider;

    /**
     * @var AppConfigInterface
     */
    protected $_app_config;

    /**
     * @var ContainerInterface
     */
    protected $_container;

    /**
     * IFace_Provider constructor
     *
     * @param IFace_Model_Provider                  $model_provider
     * @param \BetaKiller\Config\AppConfigInterface $app_config
     * @param ContainerInterface                    $container
     */
    public function __construct(IFace_Model_Provider $model_provider, AppConfigInterface $app_config, ContainerInterface $container)
    {
        $this->_model_provider = $model_provider;
        $this->_app_config     = $app_config;
        $this->_container      = $container;
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

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    public function from_model(IFaceModelInterface $model)
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

    /**
     * @param string $codename
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    protected function get_cache($codename)
    {
        return isset($this->_iface_instances[$codename])
            ? $this->_iface_instances[$codename]
            : NULL;
    }

    /**
     * @param string                            $codename
     * @param \BetaKiller\IFace\IFaceInterface  $iface
     */
    protected function set_cache($codename, IFaceInterface $iface)
    {
        $this->_iface_instances[ $codename ] = $iface;
    }

    /**
     * @param IFaceInterface $parent_iface
     * @return IFaceModelInterface[]
     * @throws IFace_Exception
     */
    public function get_models_layer(IFaceInterface $parent_iface = NULL)
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

    // TODO Move to IFaceFactory
    protected function iface_factory(IFaceModelInterface $model)
    {
        $app_ns = $this->_app_config->get_namespace();

        $codename = $model->get_codename();

        $codename_array = explode('_', $codename);

        // Add IFace prefix
        array_unshift($codename_array, 'IFace');

        $class_name = $app_ns
            ? $this->detect_iface_class_name($app_ns, $codename_array)
            : implode('_', $codename_array); // Legacy naming without namespace

        try {
            /** @var \BetaKiller\IFace\IFaceInterface $object */
            $object = $this->_container->get($class_name);
        } catch (Exception $e) {
            throw new IFace_Exception('Can not instantiate :class class for codename :codename, error is: :msg', [
                ':class'    =>  $class_name,
                ':codename' =>  $codename,
                ':msg'      =>  $e->getMessage(),
            ], $e);
        }

        if ( ! ($object instanceof \BetaKiller\IFace\IFaceInterface) )
            throw new IFace_Exception('Class :class must be instance of IFaceInterface', array(':class' => $class_name));

        $object->set_model($model);

        return $object;
    }

    private function detect_iface_class_name($app_ns, array $codename_array)
    {
        $separator = '\\';
        $common_name = implode($separator, $codename_array);

        foreach ([$app_ns, 'BetaKiller'] as $ns) {
            // Add namespace prefix
            $class_name = $ns.$separator.$common_name;

            if (class_exists($class_name)) {
                return $class_name;
            }
        }

        throw new IFace_Exception('No iface class found for :name', [':name' => $common_name]);
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    public function get_parent(\BetaKiller\IFace\IFaceInterface $iface)
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
