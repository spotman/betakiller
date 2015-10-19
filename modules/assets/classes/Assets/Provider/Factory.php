<?php defined('SYSPATH') OR die('No direct script access.');

use \BetaKiller\Utils;

class Assets_Provider_Factory {

    use Utils\Instance\Singleton,
        Utils\Factory\Cached;

    /**
     * Factory method
     *
     * @param $name
     * @return static
     */
    public function create($name)
    {
        return $this->_create($name);
    }

    protected function make_instance($class_name, $codename)
    {
        /** @var Assets_Provider $instance */
        $instance = new $class_name;
        $instance->set_codename($codename);
        return $instance;
    }

    protected function make_instance_class_name($name)
    {
        return '\\Assets_Provider_'.$name;
    }

}
