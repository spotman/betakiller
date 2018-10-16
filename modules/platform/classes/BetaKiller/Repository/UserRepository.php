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
 * @method UserInterface findById(string $id)
 * @method UserInterface getById(string $id)
 * @method UserInterface[] getAll()
 * @method User getOrmInstance()
 */
class UserRepository extends AbstractOrmBasedRepository
{
    /**
     * Search for user by username or e-mail

     * @param string $loginOrEmail
     *
     * @return \BetaKiller\Model\UserInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function searchBy(string $loginOrEmail): ?UserInterface
    {
        $orm = $this->getOrmInstance();

        $orm->where($this->getUniqueKey($loginOrEmail), '=', $loginOrEmail);

        return $this->findOne($orm);
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

    /**
     * Allows a model use both email and username as unique identifiers for login
     *
     * @param   string  unique value
     *
     * @return  string  field name
     */
    private function getUniqueKey(string $value): string
    {
        return \Valid::email($value) ? 'email' : 'username';
    }
}
