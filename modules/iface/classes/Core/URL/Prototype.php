<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Core_URL_Prototype {

    use Util_Factory_Simple;

    protected $_model_name;
    protected $_model_key;

    /**
     * @param mixed $model_key
     */
    public function set_model_key($model_key)
    {
        $this->_model_key = $model_key;
    }

    /**
     * @return mixed
     */
    public function get_model_key()
    {
        return $this->_model_key;
    }

    /**
     * @param mixed $model_name
     */
    public function set_model_name($model_name)
    {
        $this->_model_name = $model_name;
    }

    /**
     * @return mixed
     */
    public function get_model_name()
    {
        return $this->_model_name;
    }

    public function parse($string)
    {
        if ( ! $string )
            throw new Kohana_Exception('Empty url prototype string');

        $string = trim($string, '{}');

        $split = explode('.', $string);

        $this->set_model_name($split[0]);
        $this->set_model_key($split[1]);

        return $this;
    }

}