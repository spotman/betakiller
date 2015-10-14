<?php defined('SYSPATH') OR die('No direct script access.');

class Assets_Provider_Factory {

    use \Util_Instance_Singleton,
        \Util_Factory_Cached;

    protected function make_instance($class_name, $codename)
    {
        /** @var Assets_Provider $instance */
        $instance = new $class_name;
        $instance->set_codename($codename);
        return $instance;
    }

    protected function make_instance_class_name($name)
    {
        return 'Assets_Provider_'.$name;
    }

}
