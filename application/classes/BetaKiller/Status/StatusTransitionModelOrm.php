<?php
namespace BetaKiller\Workflow;

use BetaKiller\Graph\AbstractGraphTransitionModelOrm;
use BetaKiller\Model\Role;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\UserInterface;

/**
 * Class StatusTransitionModelOrm
 *
 * @package BetaKiller\Status
 * @deprecated
 */
abstract class StatusTransitionModelOrm extends AbstractGraphTransitionModelOrm implements
    StatusTransitionModelInterface
{
    protected function configure(): void
    {
        $this->has_many([
            $this->getRolesRelationKey() => [
                'model'       => 'Role',
                'foreign_key' => $this->getRolesRelationForeignKey(),
                'far_key'     => $this->getRolesRelationFarKey(),
                'through'     => $this->getRolesRelationThroughTableName(),
            ],
        ]);

        parent::configure();
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return $this
     * @deprecated
     */
    public function filterAllowedByAcl(UserInterface $user)
    {
        $throughTable = $this->getRolesRelationThroughTableName();

        $primaryKey = $this->object_primary_key();
        $foreignKey = $throughTable.'.'.$this->getRolesRelationForeignKey();
        $farKey     = $throughTable.'.'.$this->getRolesRelationFarKey();

        // inner join ACL table + where role_id in ($user->getAllUserRolesIDs())
        return $this
            ->join($throughTable, 'INNER')
            ->on($foreignKey, '=', $primaryKey)
            ->join('roles', 'INNER')
            ->on('roles.id', '=', $farKey)
            ->where('roles.name', 'IN', $user->getAllUserRolesNames());
    }

    public function addRole(RoleInterface $role)
    {
        return $this->add('roles', $role);
    }

    public function removeRole(RoleInterface $role)
    {
        return $this->remove('roles', $role);
    }

    /**
     * Returns iterator for all related roles
     *
     * @return RoleInterface[]
     */
    public function getTransitionAllowedRoles()
    {
        return $this->getRolesRelation()->get_all();
    }

    /**
     * @return string[]
     */
    public function getTransitionAllowedRolesNames()
    {
        $roles = [];

        foreach ($this->getTransitionAllowedRoles() as $role) {
            $roles[] = $role->getName();
        }

        return $roles;
    }

    /**
     * @return string
     */
    protected function getRolesRelationThroughTableName(): string
    {
        return $this->table_name().'_acl';
    }

    /**
     * @return string
     */
    protected function getRolesRelationKey(): string
    {
        return 'roles';
    }

    /**
     * @return string
     */
    protected function getRolesRelationForeignKey(): string
    {
        return 'transition_id';
    }

    /**
     * @return string
     */
    protected function getRolesRelationFarKey(): string
    {
        return 'role_id';
    }

    /**
     * @return \BetaKiller\Model\Role
     */
    protected function getRolesRelation(): Role
    {
        return $this->get($this->getRolesRelationKey());
    }
}
