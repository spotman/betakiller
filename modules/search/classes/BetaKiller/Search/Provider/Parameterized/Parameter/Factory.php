<?php
namespace BetaKiller\Search\Provider\Parameterized\Parameter;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Model;

abstract class Factory
{
    /**
     * @param string     $name
     * @param Model\User $user
     *
     * @return \BetaKiller\Search\Provider\Parameterized\ParameterInterface
     */
    public function create($name, Model\User $user = null)
    {
        throw new NotImplementedHttpException;
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
