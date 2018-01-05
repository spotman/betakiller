<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\User;
use BetaKiller\Model\UserInterface;

/**
 * Class UserRepository
 *
 * @package BetaKiller\Repository
 * @method UserInterface create()
 * @method UserInterface findById(int $id)
 * @method UserInterface[] getAll()
 * @method User getOrmInstance()
 */
class UserRepository extends AbstractOrmBasedRepository
{
    public function searchBy(string $loginOrEmail): ?UserInterface
    {
        return $this->getOrmInstance()->searchBy($loginOrEmail);
    }

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return UserInterface[]
     */
    public function getUsersWithRole(RoleInterface $role): array
    {
        // TODO Deal with roles inheritance (current implementation returns only users with explicit role)
        return $role->get_users()->get_all();
    }
}
