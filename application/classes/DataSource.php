<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class DataSource
 * @deprecated
 */
abstract class DataSource implements DataSource_Interface {

//    use Util_Factory,
//        Util_GetterAndSetterMethod;

    /**
     * @var string
     */
    protected $_name;

    /**
     * @var ORM
     */
    protected $_model;

    public function __construct($name)
    {
        $this->_name = $name;
    }

    protected static function instance_factory($name)
    {
        $class_name = __CLASS__.'_'.$name;

        if ( ! class_exists($class_name) )
            throw new HTTP_Exception_500('Class :class is absent');

        return new $class_name($name);
    }

    protected function model($name = NULL)
    {
        if ( ! $this->_model )
        {
            $this->_model = $this->model_factory($name ?: $this->_name);
        }

        return $this->_model;
    }

    protected function model_factory($name)
    {
        return ORM::factory($name);
    }

    public function find()
    {
        return $this->model()->find();
    }

    public function find_all()
    {
        return $this->model()->find_all();
    }

    /**
     * Alias of and_where()
     *
     * @param   mixed   $column  column name or array($column, $alias) or object
     * @param   string  $op      logic operator
     * @param   mixed   $value   column value
     * @return  $this
     */
    protected function where($column, $op, $value)
    {
        return $this->model()->where($column, $op, $value);
    }

}
