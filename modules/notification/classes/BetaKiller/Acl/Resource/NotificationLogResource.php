<?php
declare(strict_types=1);

namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\RoleInterface;

class NotificationLogResource extends AbstractEntityRelatedAclResource
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
        return [
            self::ACTION_CREATE => [
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_READ => [
                // Allow public actions with this entity
                RoleInterface::GUEST,
                RoleInterface::LOGIN,
            ],

            self::ACTION_UPDATE => [
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_DELETE => [
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_LIST => [
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_SEARCH => [
                RoleInterface::DEVELOPER,
            ],
        ];
    }
}
