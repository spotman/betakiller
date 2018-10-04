<?php
namespace BetaKiller\Search\Provider;

use BetaKiller\Exception\NotImplementedHttpException;

abstract class Factory
{
    /**
     * @param $name
     *
     * @return \BetaKiller\Search\Provider\AbstractProvider
     */
    public function create($name)
    {
        throw new NotImplementedHttpException;
    }
}
