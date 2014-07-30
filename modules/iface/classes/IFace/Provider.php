<?php defined('SYSPATH') OR die('No direct script access.');

class IFace_Provider {

    use Util_Singleton;

    /**
     * @param IFace $parent_iface
     * @return IFace_Model[]
     * @throws IFace_Exception
     */
    public function get_models_layer(IFace $parent_iface = NULL)
    {
        $parent_iface_model = $parent_iface ? $parent_iface->model() : NULL;

        $layer = $this->model_provider()->get_layer($parent_iface_model);

        if ( ! $layer )
            throw new IFace_Exception('Empty layer for :codename IFace',
                array(':codename' => $parent_iface->codename())
            );

        return $layer;
    }

    public function get_default()
    {
        $default_model = $this->model_provider()->get_default();

        return $this->iface_factory($default_model);
    }

    public function iface_factory(IFace_Model $model)
    {
        return IFace::factory($model);
    }

    /**
     * @return IFace_Model_Provider
     */
    protected function model_provider()
    {
        return IFace_Model_Provider::instance();
    }

}