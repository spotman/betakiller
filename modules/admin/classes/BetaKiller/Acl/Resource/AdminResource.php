<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\RoleInterface;
use Spotman\Acl\Resource\SinglePermissionResource;

class AdminResource extends SinglePermissionResource
{
    public const SHORTCUT = 'Admin.enabled';

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
                RoleInterface::ADMIN_ROLE_NAME,
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],
        ];
    }
}
