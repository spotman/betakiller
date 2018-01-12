<?php
namespace BetaKiller\Model;

use Exception;

class AclRule extends \ORM
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
        $this->_table_name = 'acl_rules';

        $this->belongs_to([
            'role' => [
                'model'       => 'Role',
                'foreign_key' => 'role_id',
            ],

            'resource' => [
                'model'       => 'AclResource',
                'foreign_key' => 'resource_id',
            ],

            'permission' => [
                'model'       => 'AclResourcePermission',
                'foreign_key' => 'permission_id',
            ],
        ]);

        $this->load_with(['resource', 'role', 'permission']);

        parent::_initialize();
    }

    /**
     * @return string
     */
    public function getAclActionIdentity()
    {
        return $this->getPermissionRelation()->getName();
    }

    /**
     * @return AclResourcePermission
     */
    private function getPermissionRelation()
    {
        return $this->get('permission');
    }

    /**
     * Null means "inherit", true - enabled, false - disabled
     *
     * @return bool|null
     */
    public function isAllowed()
    {
        $value = $this->get('is_allowed');

        return ($value === null) ? null : (bool)$value;
    }

    /**
     * @return string
     */
    public function getAclRoleIdentity()
    {
        return $this->getRoleRelation()->getName();
    }

    /**
     * @return string
     */
    public function getAclResourceIdentity()
    {
        return $this->getResourceRelation()->getCodename();
    }

    /**
     * @return $this[]
     */
    public function getAllPermissions()
    {
        return $this->get_all();
    }

    /**
     * @return \BetaKiller\Model\Role
     */
    private function getRoleRelation()
    {
        return $this->get('role');
    }

    /**
     * @return \BetaKiller\Model\AclResource
     */
    private function getResourceRelation()
    {
        return $this->get('resource');
    }
}
