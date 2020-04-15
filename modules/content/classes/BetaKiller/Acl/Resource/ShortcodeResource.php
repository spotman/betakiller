<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Helper\ContentHelper;

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
                ContentHelper::ROLE_WRITER,
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_GET_ATTRIBUTES_DEFINITION => [
                ContentHelper::ROLE_WRITER,
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_CREATE => [
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_READ => [
                ContentHelper::ROLE_WRITER,
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_UPDATE => [
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_DELETE => [
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_LIST => [
                ContentHelper::ROLE_WRITER,
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],

            self::ACTION_SEARCH => [
                ContentHelper::ROLE_WRITER,
                ContentHelper::ROLE_CONTENT_MODERATOR,
            ],
        ];
    }
}
