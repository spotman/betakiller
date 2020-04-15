<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Helper\ContentHelper;
use BetaKiller\Model\RoleInterface;

final class ContentCommentStatusResource extends AbstractEntityRelatedAclResource
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
                ContentHelper::ROLE_CONTENT_MODERATOR,
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
