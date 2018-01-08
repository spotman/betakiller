<?php
namespace BetaKiller\Search\Provider\Parameterized\Parameter;

use BetaKiller\Model;
use BetaKiller\Utils;

abstract class Factory
{

    use Utils\Factory\NamespacedFactoryTrait;

    /**
     * @param string     $name
     * @param Model\User $user
     *
     * @return \BetaKiller\Search\Provider\Parameterized\ParameterInterface
     */
    public function create($name, Model\User $user = null)
    {
        return $this->_create($name, $user);
    }

    /**
     * @param string     $class_name
     * @param Model\User $user
     *
     * @return \BetaKiller\Search\Provider\Parameterized\ParameterInterface
     */
    protected function make_instance($class_name, Model\User $user = null)
    {
        return new $class_name($user);
    }

}
