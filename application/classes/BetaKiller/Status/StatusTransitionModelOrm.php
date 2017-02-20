<?php
namespace BetaKiller\Status;

use BetaKiller\Helper\CurrentUserTrait;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Graph\GraphTransitionModelOrm;

abstract class StatusTransitionModelOrm extends GraphTransitionModelOrm
{
    use CurrentUserTrait;

    protected function _initialize()
    {
        $this->has_many([
            $this->get_roles_relation_key() => [
                'model'       => 'Role',
                'foreign_key' => $this->get_roles_relation_foreign_key(),
                'far_key'     => $this->get_roles_relation_far_key(),
                'through'     => $this->get_roles_relation_through_table_name(),
            ],
        ]);

        parent::_initialize();
    }

    /**
     * @return $this
     */
    public function filter_allowed_by_acl()
    {
        $user = $this->current_user(true);

        $through_table = $this->get_roles_relation_through_table_name();

        $primary_key = $this->object_primary_key();
        $foreign_key = $through_table.'.'.$this->get_roles_relation_foreign_key();
        $far_key     = $through_table.'.'.$this->get_roles_relation_far_key();

        // inner join ACL table + where role_id in ($user->get_roles_ids())
        return $this
            ->join($through_table, 'INNER')
            ->on($foreign_key, '=', $primary_key)
            ->where($far_key, 'IN', $user->get_roles_ids());
    }

    /**
     * Returns iterator for all related roles
     *
     * @return RoleInterface[]
     */
    public function find_all_roles()
    {
        return $this->get_roles_relation()->find_all()->as_array();
    }

    public function add_role(RoleInterface $role)
    {
        return $this->add('roles', $role);
    }

    public function remove_role(RoleInterface $role)
    {
        return $this->remove('roles', $role);
    }

    /**
     * @return string
     */
    protected function get_roles_relation_through_table_name()
    {
        return $this->table_name().'_acl';
    }

    /**
     * @return string
     */
    protected function get_roles_relation_key()
    {
        return 'roles';
    }

    /**
     * @return string
     */
    protected function get_roles_relation_foreign_key()
    {
        return 'transition_id';
    }

    /**
     * @return string
     */
    protected function get_roles_relation_far_key()
    {
        return 'role_id';
    }

    /**
     * @return RoleInterface
     */
    protected function get_roles_relation()
    {
        return $this->get($this->get_roles_relation_key());
    }
}
