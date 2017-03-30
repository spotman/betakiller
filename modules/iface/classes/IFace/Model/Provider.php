<?php
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
     * @throws IFace_Exception
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
            throw new IFace_Exception('No default IFace found');
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
     * @throws IFace_Exception
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
                throw new IFace_Exception('No IFace found by codename :codename', [':codename' => $codename]);
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
     * @throws IFace_Exception
     */
    public function get_root()
    {
        $models = [];

        foreach ($this->get_sources() as $source) {
            $models = array_merge($models, $source->get_root());
        }

        $this->cache_models($models);

        return $models;
    }

    /**
     * @return \IFace_Model_Provider_Interface[]
     */
    protected function get_sources()
    {
        if (!$this->_sources) {
            $this->_sources = [
                IFace_Model_Provider_DB::instance(),
                IFace_Model_Provider_Admin::instance(),
            ];
        }

        return $this->_sources;
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
        $this->_model_instances[$model->get_codename()] = $model;
    }
}
