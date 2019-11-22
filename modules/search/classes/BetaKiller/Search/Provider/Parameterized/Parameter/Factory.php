<?php
namespace BetaKiller\Search\Provider\Parameterized\Parameter;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Model;

abstract class Factory
{
    /**
     * @param string     $name
     *
     * @return \BetaKiller\Search\Provider\Parameterized\ParameterInterface
     */
    public function create(string $ns, string $name)
    {
        throw new NotImplementedHttpException;
    }
}
