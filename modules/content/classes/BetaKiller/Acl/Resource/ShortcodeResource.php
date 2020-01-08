<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Content\Content;

final class ShortcodeResource extends AbstractEntityRelatedAclResource
{
    public const ACTION_VERIFY                    = 'verify';
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
                Content::ROLE_WRITER,
                Content::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_GET_ATTRIBUTES_DEFINITION => [
                Content::ROLE_WRITER,
                Content::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_CREATE => [
                Content::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_READ => [
                Content::ROLE_WRITER,
                Content::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_UPDATE => [
                Content::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_DELETE => [
                Content::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_LIST => [
                Content::ROLE_WRITER,
                Content::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_SEARCH => [
                Content::ROLE_WRITER,
                Content::ROLE_CONTENT_MODERATOR,
            ],
        ];
    }
}
