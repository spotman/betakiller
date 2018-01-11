<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\RoleInterface;

class ShortcodeResource extends AbstractEntityRelatedAclResource
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
                RoleInterface::ADMIN_ROLE_NAME,
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],

            self::ACTION_READ => [
                RoleInterface::WRITER_ROLE_NAME,
                RoleInterface::MODERATOR_ROLE_NAME,
                RoleInterface::ADMIN_ROLE_NAME,
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],

            self::ACTION_UPDATE => [
                RoleInterface::MODERATOR_ROLE_NAME,
                RoleInterface::ADMIN_ROLE_NAME,
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],

            self::ACTION_DELETE => [
                RoleInterface::MODERATOR_ROLE_NAME,
                RoleInterface::ADMIN_ROLE_NAME,
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],

            self::ACTION_LIST => [
                RoleInterface::WRITER_ROLE_NAME,
                RoleInterface::MODERATOR_ROLE_NAME,
                RoleInterface::ADMIN_ROLE_NAME,
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],

            self::ACTION_SEARCH => [
                RoleInterface::WRITER_ROLE_NAME,
                RoleInterface::MODERATOR_ROLE_NAME,
                RoleInterface::ADMIN_ROLE_NAME,
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],
        ];
    }
}
