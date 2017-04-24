<?php
namespace BetaKiller\Search\Provider\Parameterized\Parameter;

use BetaKiller\Utils;
use BetaKiller\Model;

abstract class Factory {

    use Utils\Instance\Simple,
        Utils\Factory\Namespaced;

    /**
     * @param string     $name
     * @param Model\User $user
     *
     * @return \BetaKiller\Search\Provider\Parameterized\Parameter
     */
    public function create($name, Model\User $user = NULL)
    {
        return $this->_create($name, $user);
    }

    /**
     * @param string     $class_name
     * @param Model\User $user
     *
     * @return \BetaKiller\Search\Provider\Parameterized\Parameter
     */
    protected function make_instance($class_name, Model\User $user = NULL)
    {
        return new $class_name($user);
    }

}
