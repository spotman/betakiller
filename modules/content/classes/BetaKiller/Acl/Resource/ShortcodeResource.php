<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Content\Content;
use BetaKiller\Model\RoleInterface;

class ShortcodeResource extends AbstractEntityRelatedAclResource
{
    public const ACTION_VERIFY = 'verify';
    public const ACTION_GET_ATTRIBUTES_DEFINITION = 'getAttributesDefinition';

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
            self::ACTION_VERIFY => [
                Content::WRITER_ROLE_NAME,
                RoleInterface::MODERATOR,
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_GET_ATTRIBUTES_DEFINITION => [
                Content::WRITER_ROLE_NAME,
                RoleInterface::MODERATOR,
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_CREATE => [
                RoleInterface::MODERATOR,
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_READ => [
                Content::WRITER_ROLE_NAME,
                RoleInterface::MODERATOR,
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_UPDATE => [
                RoleInterface::MODERATOR,
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_DELETE => [
                RoleInterface::MODERATOR,
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_LIST => [
                Content::WRITER_ROLE_NAME,
                RoleInterface::MODERATOR,
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_SEARCH => [
                Content::WRITER_ROLE_NAME,
                RoleInterface::MODERATOR,
                RoleInterface::DEVELOPER,
            ],
        ];
    }
}
