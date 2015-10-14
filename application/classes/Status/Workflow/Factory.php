<?php defined('SYSPATH') OR die('No direct script access.');

class Status_Workflow_Factory {

    use \Util_Factory,
        \Util_Instance_Singleton;

    /**
     * @param $name
     * @param Status_Related_Model $model
     * @return static
     */
    public static function create($name, Status_Related_Model $model)
    {
        return static::instance_factory($name, $model);
    }

    protected function make_instance($class_name, $name, Status_Related_Model $model)
    {
        return new $class_name($model);
    }

}
