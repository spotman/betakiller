<?php defined('SYSPATH') OR die('No direct script access.');

trait Util_Factory {

    /**
     * @param $name
     * @return static
     * @throws HTTP_Exception_500
     */
    public static function factory($name)
    {
        $class_name = __CLASS__.'_'.$name;

        if ( ! class_exists($class_name) )
            throw new HTTP_Exception_500('Class :class is absent');

        return new $class_name;
    }

}