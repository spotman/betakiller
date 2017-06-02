<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\Role;

class ContentCategoryResource extends AbstractEntityRelatedAclResource
{
    /**
     * Returns default permissions bundled with current resource
     * Key=>Value pairs where key is a permission identity and value is an array of roles
     * Useful for presetting permissions for resources with fixed access control list or permissions based on hard-coded logic
     *
     * @return string[][]
     */
    public function getDefaultAccessList()
    {
        return [
            self::ACTION_CREATE => [
                Role::MODERATOR_ROLE_NAME,
            ],

            self::ACTION_READ => [
                Role::GUEST_ROLE_NAME,
            ],

            self::ACTION_UPDATE => [
                Role::MODERATOR_ROLE_NAME,
            ],

            self::ACTION_DELETE => [
                Role::ADMIN_ROLE_NAME,
            ],

            self::ACTION_LIST => [
                Role::GUEST_ROLE_NAME,
            ],

            self::ACTION_SEARCH => [
                Role::GUEST_ROLE_NAME,
            ],
        ];
    }
}
