<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
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
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUsersWithRole(RoleInterface $role): array
    {
        $orm = $this->getOrmInstance();

        $this->filterRole($orm, $role);

        return $this->findAll($orm);
    }

    private function filterRole(ExtendedOrmInterface $orm, RoleInterface $role): self
    {
        // TODO Deal with roles inheritance (current implementation returns only users with explicit role)
        $orm->join_related('roles', 'roles');
        $orm->where('roles.id', '=', $role->getID());

        return $this;
    }
}
