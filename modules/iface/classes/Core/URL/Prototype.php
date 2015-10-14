<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Core_URL_Prototype {

    use Util_Factory_Simple;

    protected $_model_name;
    protected $_model_key;
    protected $_is_method_call = FALSE;

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

    /**
     * @return bool
     */
    public function is_method_call()
    {
        return $this->_is_method_call;
    }

//    /**
//     * @param bool $is_method_call
//     */
//    public function set_is_method_call($is_method_call)
//    {
//        $this->_is_method_call = (bool) $is_method_call;
//    }

    public function parse($string)
    {
        if ( ! $string )
            throw new Kohana_Exception('Empty url prototype string');

        $string = trim($string, '{}');

        $model = explode('.', $string);
        $name = $model[0];
        $key = $model[1];

        if ( strpos($key, '()') !== FALSE )
        {
            $this->_is_method_call = TRUE;
            $key = str_replace('()', '', $key);
        }

        $this->set_model_name($name);
        $this->set_model_key($key);

        return $this;
    }

}
