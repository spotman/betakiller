<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\RoleInterface;

class ContentCategoryResource extends AbstractEntityRelatedAclResource
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
                RoleInterface::MODERATOR_ROLE_NAME,
            ],

            self::ACTION_READ => [
                RoleInterface::GUEST_ROLE_NAME,
            ],

            self::ACTION_UPDATE => [
                RoleInterface::MODERATOR_ROLE_NAME,
            ],

            self::ACTION_DELETE => [
                RoleInterface::ADMIN_ROLE_NAME,
            ],

            self::ACTION_LIST => [
                RoleInterface::GUEST_ROLE_NAME,
            ],

            self::ACTION_SEARCH => [
                RoleInterface::GUEST_ROLE_NAME,
            ],
        ];
    }
}
