<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Content\Content;
use BetaKiller\Model\RoleInterface;

class ContentPostRevisionResource extends AbstractEntityRelatedAclResource
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
                RoleInterface::MODERATOR,
                RoleInterface::DEVELOPER,
                Content::WRITER_ROLE_NAME,
            ],
            self::ACTION_READ => [
                RoleInterface::MODERATOR,
                RoleInterface::DEVELOPER,
                Content::WRITER_ROLE_NAME,
            ],
            self::ACTION_UPDATE => [
                RoleInterface::DEVELOPER,
            ],
            self::ACTION_DELETE => [
                RoleInterface::DEVELOPER,
            ],
            self::ACTION_LIST => [
                RoleInterface::MODERATOR,
                RoleInterface::DEVELOPER,
                Content::WRITER_ROLE_NAME,
            ],
            self::ACTION_SEARCH => [
                RoleInterface::MODERATOR,
                RoleInterface::DEVELOPER,
                Content::WRITER_ROLE_NAME,
            ],
        ];
    }
}
