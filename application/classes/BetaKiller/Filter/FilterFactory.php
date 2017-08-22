<?php
namespace BetaKiller\Filter;

use BetaKiller\Model\User;
use BetaKiller\Utils;

class FilterFactory
{
    use Utils\Factory\Namespaced,
        Utils\Instance\Simple;

    /**
     * Factory method
     *
     * @param                             $name
     * @param \BetaKiller\Model\User|null $user
     *
     * @return mixed|\BetaKiller\Filter\FilterInterface
     */
    public function create($name, User $user = null): FilterInterface
    {
        return $this->_create($name, $user);
    }
}
