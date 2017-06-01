<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\Role;

class PhpExceptionResource extends AbstractEntityRelatedAclResource
{
    const PERMISSION_LIST_RESOLVED   = 'listResolved';
    const PERMISSION_LIST_UNRESOLVED = 'listUnresolved';
    const PERMISSION_RESOLVE         = 'resolve';
    const PERMISSION_IGNORE          = 'ignore';
    const PERMISSION_DELETE          = 'delete';
    const PERMISSION_TEST            = 'test';

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
                Role::GUEST_ROLE_NAME,
            ],

            self::READ_ACTION => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::UPDATE_ACTION => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::DELETE_ACTION => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::SEARCH_ACTION => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_LIST_RESOLVED => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_LIST_UNRESOLVED => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_LIST_UNRESOLVED => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_IGNORE => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_DELETE => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_TEST => [
                Role::DEVELOPER_ROLE_NAME,
            ],
        ];
    }
}
