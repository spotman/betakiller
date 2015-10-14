<?php defined('SYSPATH') OR die('No direct script access.');

class Status_Workflow_Factory {

    use \Util_Factory,
        \Util_Instance_Singleton {
        \Util_Factory::create as protected _create;
    }

    /**
     * @param $name
     * @param Status_Related_Model $model
     * @return static
     */
    public function create($name, Status_Related_Model $model)
    {
        return $this->_create($name, $model);
    }

    protected function make_instance_class_name($name)
    {
        return 'Status_Workflow_'.$name;
    }

    protected function make_instance($class_name, $name, Status_Related_Model $model)
    {
        return new $class_name($model);
    }

}
