<?php defined('SYSPATH') OR die('No direct script access.');

abstract class IFace_Provider {

    use Util_Factory;

    /**
     * @param $codename
     * @return IFace_Model
     */
    abstract public function by_codename($codename);


    /**
     * Returns default iface model in current provider
     *
     * @return IFace_Model
     */
    abstract public function get_default();


    /**
     * Returns parent iface model
     *
     * @param IFace_Model $model
     * @return IFace_Model
     */
    abstract public function get_parent(IFace_Model $model);

    protected function model_factory()
    {
        $model = new IFace_Model;

        $model->provider($this);
    }
}