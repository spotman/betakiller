<?php

use BetaKiller\Model\RoleInterface;

class Model_AclPermission extends \ORM
{
    protected $_table_name = 'acl_permissions';

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->belongs_to([
            'role' =>  [
                'model'         =>  'Role',
                'foreign_key'   =>  'role_id',
            ],

            'resource' => [
                'model'         =>  'AclResource',
                'foreign_key'   =>  'resource_id'
            ],
        ]);


        $this->load_with(['resource', 'role']);

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
    public function get_name()
    {
        return $this->get('name');
    }

    /**
     * Null means "inherit", true - enabled, false - disabled
     * @return bool|null
     */
    public function is_allowed()
    {
        $value = $this->get('is_allowed');
        return is_null($value) ? $value : (bool) $value;
    }

    /**
     * @return string
     */
    public function get_acl_role_identity()
    {
        $role = $this->get_role_relation();
        return ($role && $role->loaded()) ? $role->get_name() : null;
    }

    /**
     * @return string
     */
    public function get_acl_resource_identity()
    {
        $resource = $this->get_resource_relation();
        return ($resource && $resource->loaded()) ? $resource->getResourceId() : null;
    }

    /**
     * @return $this[]
     */
    public function get_all_permissions()
    {
        return $this->get_all();
    }

    /**
     * @return RoleInterface
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
