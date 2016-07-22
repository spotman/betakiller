<?php defined('SYSPATH') OR die('No direct script access.');

class Assets_Storage_Factory {

    /**
     * @var Assets_Storage_Factory
     */
    protected static $_instance;

    public static function instance()
    {
        if ( !static::$_instance )
            static::$_instance = new static();

        return static::$_instance;
    }

    /**
     * @param string $codename
     * @return Assets_Storage_Local|Assets_Storage_CFS
     * @throws Assets_Storage_Exception
     */
    public function create($codename)
    {
        $class_name = 'Assets_Storage_'.$codename;

        if ( ! class_exists($class_name) )
            throw new Assets_Storage_Exception('Unknown storage :class', array(':class' => $class_name));

        return new $class_name;
    }

}
