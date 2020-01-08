<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Content\Content;
use BetaKiller\Model\RoleInterface;

final class ContentCategoryResource extends AbstractEntityRelatedAclResource
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
                Content::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_READ => [
                RoleInterface::GUEST,
            ],

            self::ACTION_UPDATE => [
                Content::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_DELETE => [
                Content::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_LIST => [
                RoleInterface::GUEST,
            ],

            self::ACTION_SEARCH => [
                RoleInterface::GUEST,
            ],
        ];
    }
}
