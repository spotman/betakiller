<?php
namespace BetaKiller\Search\Provider;

use BetaKiller\Utils;

abstract class Factory
{
    use Utils\Factory\NamespacedFactoryTrait;

    /**
     * @param $name
     *
     * @return \BetaKiller\Search\Provider\AbstractProvider
     */
    public function create($name)
    {
        return $this->_create($name);
    }
}
