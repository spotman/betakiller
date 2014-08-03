<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Status_Workflow {

    use Util_Factory;

    /**
     * @var Status_Related_Model
     */
    protected $_model;

    /**
     * @param $name
     * @param Status_Related_Model $model
     * @return static
     */
    public static function factory($name, Status_Related_Model $model)
    {
        return static::instance_factory($name, $model);
    }

    public function __construct(Status_Related_Model $model)
    {
        $this->_model = $model;
    }

}
