<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\RoleInterface;

class MissingUrlResource extends AbstractEntityRelatedAclResource
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
                RoleInterface::GUEST,
            ],

            self::ACTION_READ => [
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_UPDATE => [
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_DELETE => [
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_SEARCH => [
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_LIST => [
                RoleInterface::DEVELOPER,
            ],
        ];
    }
}
