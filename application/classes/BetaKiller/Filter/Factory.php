<?php
namespace BetaKiller\Filter;

use BetaKiller\Model\User;
use \BetaKiller\Utils;

class Factory
{
    use Utils\Factory\Namespaced,
        Utils\Instance\Simple;

    /**
     * Factory method
     *
     * @param                             $name
     * @param \BetaKiller\Model\User|null $user
     *
     * @return mixed|\BetaKiller\Filter\Base
     */
    public function create($name, User $user = null)
    {
        return $this->_create($name, $user);
    }
}
