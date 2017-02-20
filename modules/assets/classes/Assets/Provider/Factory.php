<?php defined('SYSPATH') OR die('No direct script access.');

class Assets_Provider_Factory
{
    use BetaKiller\Utils\Instance\SingletonTrait,
        BetaKiller\Utils\Factory\BaseFactoryTrait,
        BetaKiller\DI\ContainerTrait;

    /**
     * Factory method
     *
     * @param $name
     * @return Assets_Provider|Assets_Provider_Image
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

    protected function make_instance($class_name, ...$parameters)
    {
        return $this->getContainer()->make($class_name, $parameters);
    }
}
