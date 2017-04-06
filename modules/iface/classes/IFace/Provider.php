<?php

use BetaKiller\Factory\NamespaceBasedFactory;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;

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
     * @var \BetaKiller\Factory\NamespaceBasedFactory
     */
    protected $_factory;

    /**
     * IFace_Provider constructor
     *
     * @param IFace_Model_Provider  $model_provider
     * @param NamespaceBasedFactory $factory
     */
    public function __construct(IFace_Model_Provider $model_provider, NamespaceBasedFactory $factory)
    {
        $this->_model_provider = $model_provider;
        $this->_factory        = $factory;
    }

    public function by_codename($codename)
    {
        $iface = $this->get_cache($codename);

        if (!$iface) {
            $model = $this->model_provider()->by_codename($codename);
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
            : null;
    }

    /**
     * @return IFace_Model_Provider
     */
    protected function model_provider()
    {
        return $this->_model_provider;
    }

    // TODO Move to IFaceFactory
    protected function iface_factory(IFaceModelInterface $model)
    {
        $codename = $model->get_codename();

        /** @var \BetaKiller\IFace\IFaceInterface $object */
        $object = $this->_factory
            ->setClassPrefixes('IFace')
            ->setExpectedInterface(IFaceInterface::class)
            ->create($codename);

        $object->set_model($model);

        return $object;
    }

    /**
     * @param string                           $codename
     * @param \BetaKiller\IFace\IFaceInterface $iface
     */
    protected function set_cache($codename, IFaceInterface $iface)
    {
        $this->_iface_instances[$codename] = $iface;
    }

    /**
     * @param IFaceInterface $parent_iface
     *
     * @return IFaceModelInterface[]
     * @throws IFace_Exception
     */
    public function get_models_layer(IFaceInterface $parent_iface = null)
    {
        $parent_iface_model = $parent_iface ? $parent_iface->get_model() : null;

        $layer = $this->model_provider()->get_layer($parent_iface_model);

        if (!$layer) {
            throw new IFace_Exception('Empty layer for :codename IFace',
                [':codename' => $parent_iface->get_codename()]
            );
        }

        return $layer;
    }

    public function get_default()
    {
        $default_model = $this->model_provider()->get_default();

        return $this->iface_factory($default_model);
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    public function get_parent(\BetaKiller\IFace\IFaceInterface $iface)
    {
        $model        = $iface->get_model();
        $parent_model = $this->model_provider()->get_parent($model);

        return $parent_model
            ? $this->from_model($parent_model)
            : null;
    }

    /**
     * @param \BetaKiller\IFace\IFaceModelInterface $model
     *
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    public function from_model(IFaceModelInterface $model)
    {
        $codename = $model->get_codename();
        $iface    = $this->get_cache($codename);

        if (!$iface) {
            $iface = $this->iface_factory($model);
            $this->set_cache($codename, $iface);
        }

        return $iface;
    }
}
