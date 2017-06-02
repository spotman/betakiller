<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\Role;
use Spotman\Acl\Resource\SinglePermissionResource;

class AdminResource extends SinglePermissionResource
{
    /**
     * Returns default permissions bundled with current resource
     * Useful for presetting permissions for resources with fixed access control list or permissions based on hard-coded logic
     *
     * @return array Key=>Value pairs where key is a permission identity and value is an array of roles
     */
    public function getDefaultAccessList(): array
    {
        // No default permissions
        return [
            self::PERMISSION_IDENTITY   =>  [
                Role::ADMIN_ROLE_NAME,
                Role::DEVELOPER_ROLE_NAME,
            ],
        ];
    }
}
