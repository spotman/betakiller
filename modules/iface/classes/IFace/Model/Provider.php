<?php
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceModelInterface;

class IFace_Model_Provider implements IFace_Model_Provider_Interface
{
    /**
     * @var \IFace_Model_Provider_Interface[]
     */
    protected $_sources;

    /**
     * @var IFaceModelInterface[]
     */
    protected $_model_instances = [];

    /**
     * Returns default iface model in current provider
     *
     * @return IFaceModelInterface|null
     * @throws IFaceException
     */
    public function get_default()
    {
        $model = NULL;

        foreach ($this->get_sources() as $source) {
            if ($model = $source->get_default()) {
                break;
            }
        }

        if (!$model) {
            throw new IFaceException('No default IFace found');
        }

        $this->set_cache($model);

        return $model;
    }

    /**
     * Returns iface model by codename or NULL if none was found
     *
     * @param $codename
     *
     * @return IFaceModelInterface|null
     * @throws IFaceException
     */
    public function by_codename($codename)
    {
        $model = $this->get_cache($codename);

        if (!$model) {
            foreach ($this->get_sources() as $source) {
                if ($model = $source->by_codename($codename)) {
                    break;
                }
            }

            if (!$model) {
                throw new IFaceException('No IFace found by codename :codename', [':codename' => $codename]);
            }

            $this->set_cache($model);
        }

        return $model;
    }

    /**
     * @param IFaceModelInterface $parent_model
     *
     * @return IFaceModelInterface[]
     */
    public function get_layer(IFaceModelInterface $parent_model = NULL)
    {
        return $parent_model
            ? $this->get_children($parent_model)
            : $this->get_root();
    }

    public function get_children(IFaceModelInterface $parent_model)
    {
        $models = $parent_model->get_children();

        $this->cache_models($models);

        return $models;
    }

    /**
     * @param IFaceModelInterface $model
     *
     * @return IFaceModelInterface|NULL
     */
    public function get_parent(IFaceModelInterface $model)
    {
        $parent = $model->get_parent();

        if ($parent) {
            $this->set_cache($parent);
        }

        return $parent;
    }

    /**
     * Returns list of root elements
     *
     * @return IFaceModelInterface[]
     * @throws IFaceException
     */
    public function get_root()
    {
        /** @var IFaceModelInterface[] $models */
        $models = [];

        foreach ($this->get_sources(true) as $source) {
            $root = $source->get_root();

            foreach ($root as $item) {
                $models[$item->getCodename()] = $item;
            }
        }

        $this->cache_models($models);

        return $models;
    }

    /**
     * @param bool $reverse
     * @return \IFace_Model_Provider_Interface[]
     */
    protected function get_sources($reverse = false)
    {
        if (!$this->_sources) {
            $this->_sources = [
                IFace_Model_Provider_DB::getInstance(),
                IFace_Model_Provider_Admin::getInstance(),
            ];
        }

        return $reverse ? array_reverse($this->_sources) : $this->_sources;
    }

    /**
     * @param IFaceModelInterface[] $models
     */
    protected function cache_models(array $models)
    {
        foreach ($models as $model) {
            $this->set_cache($model);
        }
    }

    /**
     * @param string $codename
     *
     * @return IFaceModelInterface|NULL
     */
    protected function get_cache($codename)
    {
        return isset($this->_model_instances[$codename])
            ? $this->_model_instances[$codename]
            : NULL;
    }

    protected function set_cache(IFaceModelInterface $model)
    {
        $this->_model_instances[$model->getCodename()] = $model;
    }
}
