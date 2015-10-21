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

    /**
     * @param \Assets_Provider $instance
     * @param $codename
     */
    protected function store_codename($instance, $codename)
    {
        $instance->set_codename($codename);
    }

    protected function make_instance_class_name($name)
    {
        return '\\Assets_Provider_'.$name;
    }

}
