<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Status_Transition_Model extends Graph_Transition_Model {

    protected function _initialize()
    {
        $this->has_many([
            $this->get_roles_relation_key()   =>  [
                'model'         =>  'Role',
                'foreign_key'   =>  $this->get_roles_relation_foreign_key(),
                'far_key'       =>  $this->get_roles_relation_far_key(),
                'through'       =>  $this->get_roles_relation_through_table_name(),
            ],
        ]);

        parent::_initialize();
    }

    /**
     * @return $this
     */
    public function filter_allowed_by_acl()
    {
        $user = Env::user(TRUE);

        $through_table = $this->get_roles_relation_through_table_name();

        $primary_key = $this->object_name().'.'.$this->primary_key();
        $foreign_key = $through_table.'.'.$this->get_roles_relation_foreign_key();
        $far_key = $through_table.'.'.$this->get_roles_relation_far_key();

        // inner join ACL table + where role_id in ($user->get_roles_ids())
        return $this
            ->join($through_table, 'INNER')
            ->on($foreign_key, '=', $primary_key)
            ->where($far_key, 'IN', $user->get_roles_ids());
    }

    /**
     * Returns iterator for all related roles
     *
     * @return Database_Result|Model_Role[]
     */
    public function find_all_roles()
    {
        return $this->get_roles_relation()->find_all();
    }

    public function add_role(Model_Role $role)
    {
        return $this->add('roles', $role);
    }

    public function remove_role(Model_Role $role)
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
     * @return Model_Role
     */
    protected function get_roles_relation()
    {
        return $this->get($this->get_roles_relation_key());
    }

}
