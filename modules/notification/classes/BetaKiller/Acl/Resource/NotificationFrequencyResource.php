<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\RoleInterface;

class NotificationFrequencyResource extends AbstractEntityRelatedAclResource
{
    /**
     * Returns default permissions bundled with current resource
     * Key=>Value pairs where key is a permission identity and value is an array of roles
     * Useful for presetting permissions for resources with fixed access control list or permissions based on hard-coded logic
     *
     * @return string[][]
     */
    public function getDefaultAccessList(): array
    {
        $devRole = [
            RoleInterface::DEVELOPER,
        ];

        $userRoles = [
            RoleInterface::LOGIN,
        ];

        $adminRoles = [
            RoleInterface::ADMIN_PANEL,
        ];

        return [
            self::ACTION_CREATE => $devRole,
            self::ACTION_READ   => $devRole,
            self::ACTION_UPDATE => $devRole,
            self::ACTION_DELETE => $devRole,
            self::ACTION_LIST   => $userRoles,
            self::ACTION_SEARCH => $adminRoles,
        ];
    }
}
