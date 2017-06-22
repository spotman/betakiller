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
    public function getDefaultAccessList(): array
    {
        return [
            self::ACTION_CREATE => [
                Role::GUEST_ROLE_NAME,
            ],

            self::ACTION_READ => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::ACTION_UPDATE => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::ACTION_DELETE => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::ACTION_SEARCH => [
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

            self::PERMISSION_RESOLVE => [
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
