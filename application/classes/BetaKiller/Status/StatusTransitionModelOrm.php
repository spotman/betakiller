<?php
namespace BetaKiller\Status;

use BetaKiller\Graph\AbstractGraphTransitionModelOrm;
use BetaKiller\Model\Role;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\UserInterface;

abstract class StatusTransitionModelOrm extends AbstractGraphTransitionModelOrm implements StatusTransitionModelInterface
{
    protected function _initialize()
    {
        $this->has_many([
            $this->getRolesRelationKey() => [
                'model'       => 'Role',
                'foreign_key' => $this->getRolesRelationForeignKey(),
                'far_key'     => $this->getRolesRelationFarKey(),
                'through'     => $this->getRolesRelationThroughTableName(),
            ],
        ]);

        parent::_initialize();
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return $this
     */
    public function filterAllowedByAcl(UserInterface $user)
    {
        $through_table = $this->getRolesRelationThroughTableName();

        $primary_key = $this->object_primary_key();
        $foreign_key = $through_table.'.'.$this->getRolesRelationForeignKey();
        $far_key     = $through_table.'.'.$this->getRolesRelationFarKey();

        // inner join ACL table + where role_id in ($user->getAllUserRolesIDs())
        return $this
            ->join($through_table, 'INNER')
            ->on($foreign_key, '=', $primary_key)
            ->where($far_key, 'IN', $user->getAllUserRolesIDs());
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
