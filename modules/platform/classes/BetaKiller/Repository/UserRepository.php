<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\User;
use BetaKiller\Model\UserInterface;
use BetaKiller\Model\UserState;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Class UserRepository
 *
 * @package BetaKiller\Repository
 * @method UserInterface findById(string $id)
 * @method UserInterface getById(string $id)
 * @method UserInterface[] getAll()
 * @method void save(UserInterface $entity)
 */
class UserRepository extends AbstractHasWorkflowStateRepository implements UserRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function getUrlKeyName(): string
    {
        return User::COL_ID;
    }

    /**
     * Search for user by username or e-mail
     *
     * @param string $loginOrEmail
     *
     * @return \BetaKiller\Model\UserInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function searchBy(string $loginOrEmail): ?UserInterface
    {
        $orm = $this->getOrmInstance();

        // Lowercase to prevent collisions
        $loginOrEmail = \mb_strtolower($loginOrEmail);

        $orm->where($this->getUniqueKey($loginOrEmail), '=', $loginOrEmail);

        return $this->findOne($orm);
    }

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @param bool|null                       $checkNested
     *
     * @return UserInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUsersWithRole(RoleInterface $role, bool $checkNested = null): array
    {
        return $this->getUsersWithRoles([$role], $checkNested);
    }

    /**
     * @param \BetaKiller\Model\RoleInterface[] $roles
     *
     * @param bool|null                         $checkNested
     *
     * @return UserInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUsersWithRoles(array $roles, bool $checkNested = null): array
    {
        $orm = $this->getOrmInstance();

        if ($checkNested) {
            $nestedRoles = [];

            foreach ($roles as $role) {
                foreach ($role->getAllChilds() as $childRole) {
                    $nestedRoles[] = $childRole;
                }
            }

            $roles = array_merge($roles, $nestedRoles);
        }

        // Multiple roles => possible duplicates
        $orm->group_by_primary_key();

        return $this
            ->filterRoles($orm, $roles)
            ->findAll($orm);
    }

    protected function getStateRelationKey(): string
    {
        return User::getWorkflowStateRelationKey();
    }

    protected function getStateCodenameColumnName(): string
    {
        return UserState::COL_CODENAME;
    }

    private function filterRole(ExtendedOrmInterface $orm, RoleInterface $role): self
    {
        $orm->join_related('roles', 'roles');
        $orm->where('roles.id', '=', $role->getID());

        return $this;
    }

    /**
     * @param OrmInterface    $orm
     * @param RoleInterface[] $roles
     *
     * @return $this
     */
    private function filterRoles(OrmInterface $orm, array $roles): self
    {
        $ids = \array_unique(\array_map(static function (RoleInterface $role) {
            return $role->getID();
        }, $roles));

        $orm->join_related('roles', 'roles');
        $orm->where('roles.id', 'IN', $ids);

        return $this;
    }

    /**
     * Allows a model use both email and username as unique identifiers for login
     *
     * @param string  unique value
     *
     * @return  string  field name
     */
    private function getUniqueKey(string $value): string
    {
        return \Valid::email($value) ? 'email' : 'username';
    }
}
