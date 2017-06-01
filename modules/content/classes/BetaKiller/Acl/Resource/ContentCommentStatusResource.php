<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\Role;

class ContentCommentStatusResource extends AbstractEntityRelatedAclResource
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
            self::CREATE_ACTION => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::READ_ACTION => [
                Role::MODERATOR_ROLE_NAME,
            ],

            self::UPDATE_ACTION => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::DELETE_ACTION => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::LIST_ACTION => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::SEARCH_ACTION => [
                Role::DEVELOPER_ROLE_NAME,
            ],
        ];
    }
}
