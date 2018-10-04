<?php
namespace BetaKiller\Filter;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Model\User;

class FilterFactory
{
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
        throw new NotImplementedHttpException;
    }
}
