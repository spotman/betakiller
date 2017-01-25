<?php
namespace BetaKiller\Acl;

use Spotman\Acl\PermissionsCollector\AbstractPermissionsCollector;
use Model_AclPermission;

class PermissionsCollector extends AbstractPermissionsCollector
{
    /**
     * @var Model_AclPermission
     */
    private $permissionModel;

    /**
     * PermissionsCollector constructor.
     *
     * @param Model_AclPermission $permissionModel
     */
    public function __construct(Model_AclPermission $permissionModel)
    {
        $this->permissionModel = $permissionModel;
    }

    /**
     * Collect entities from external source and add them to acl via protected methods addAllowRule / addDenyRule
     */
    public function collectPermissions()
    {
        $permissions = $this->permissionModel->get_all_permissions();

        foreach ($permissions as $permission) {
            $role                       = $permission->get_acl_role_identity();
            $actionPermissionIdentity   = $permission->get_acl_action_identity();
            $actionResourceIdentity     = $permission->get_acl_action_resource_identity();
            $bindToResourceIdentity     = $permission->get_acl_resource_identity();

            $value = $permission->is_allowed();

            if ($value === true) {
                $this->addAllowRule($role, $actionResourceIdentity, $actionPermissionIdentity, $bindToResourceIdentity);
            } else if ($value === false) {
                $this->addDenyRule($role, $actionResourceIdentity, $actionPermissionIdentity, $bindToResourceIdentity);
            }
        }
    }
}
