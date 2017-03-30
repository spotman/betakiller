<?php

class Model_AclPermission extends \ORM
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->_table_name = 'acl_permissions';

        $this->belongs_to([
            'role' =>  [
                'model'         =>  'Role',
                'foreign_key'   =>  'role_id',
            ],

            'resource' => [
                'model'         =>  'AclResource',
                'foreign_key'   =>  'resource_id'
            ],

            'action' => [
                'model'         =>  'AclResourceAction',
                'foreign_key'   =>  'action_id'
            ],
        ]);

        $this->load_with(['resource', 'role', 'action', 'action:resource']);

        parent::_initialize();
    }

    /**
     * Place here additional query params
     */
    protected function additional_tree_model_filtering()
    {
        // Nothing to do
    }

    /**
     * @return string
     */
    public function get_acl_action_identity()
    {
        return $this->get_action_relation()->get_name();
    }

    /**
     * @return string
     */
    public function get_acl_action_resource_identity()
    {
        return $this->get_action_relation()->get_resource_identity();
    }

    /**
     * @return Model_AclResourceAction
     */
    protected function get_action_relation()
    {
        return $this->get('action');
    }

    /**
     * Null means "inherit", true - enabled, false - disabled
     * @return bool|null
     */
    public function is_allowed()
    {
        $value = $this->get('is_allowed');
        return ($value === null) ? $value : (bool) $value;
    }

    /**
     * @return string
     */
    public function get_acl_role_identity()
    {
        return $this->get_role_relation()->get_name();
    }

    /**
     * @return string
     */
    public function get_acl_resource_identity()
    {
        return $this->get_resource_relation()->getCodename();
    }

    /**
     * @return $this[]
     */
    public function get_all_permissions()
    {
        return $this->get_all();
    }

    /**
     * @return \BetaKiller\Model\Role
     */
    protected function get_role_relation()
    {
        return $this->get('role');
    }

    /**
     * @return \Model_AclResource
     */
    protected function get_resource_relation()
    {
        return $this->get('resource');
    }
}
