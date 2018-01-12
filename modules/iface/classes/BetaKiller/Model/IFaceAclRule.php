<?php
namespace BetaKiller\Model;

use ORM;

class IFaceAclRule extends ORM
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \BetaKiller\Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->_table_name = 'iface_acl_rules';

        $this->belongs_to([
            'resource'  => [
                'model' => 'AclResource',
                'foreign_key' => 'resource_id',
            ],
            'permission' => [
                'model' => 'AclResourcePermission',
                'foreign_key' => 'permission_id',
            ],
        ]);

        $this->load_with([
            'resource',
            'permission'
        ]);

        parent::_initialize();
    }

    public function getCombinedRule()
    {
        return implode('.', [$this->getResourceCodename(), $this->getResourcePermissionName()]);
    }

    /**
     * @return \BetaKiller\Model\AclResource
     */
    public function getResource()
    {
        return $this->get('resource');
    }

    /**
     * @return string
     */
    public function getResourceCodename()
    {
        return $this->getResource()->getCodename();
    }

    /**
     * @return \BetaKiller\Model\AclResourcePermission
     */
    public function getResourcePermission()
    {
        return $this->get('permission');
    }

    /**
     * @return string
     */
    public function getResourcePermissionName()
    {
        return $this->getResourcePermission()->getName();
    }
}
