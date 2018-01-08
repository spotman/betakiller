<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\RoleInterface;

class PhpExceptionResource extends AbstractEntityRelatedAclResource
{
    const PERMISSION_LIST_RESOLVED   = 'listResolved';
    const PERMISSION_LIST_UNRESOLVED = 'listUnresolved';
    const PERMISSION_RESOLVE         = 'resolve';
    const PERMISSION_IGNORE          = 'ignore';
    const PERMISSION_DELETE          = 'delete';
    const PERMISSION_TEST            = 'test';
    const PERMISSION_THROW           = 'throwHttpException';

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
                RoleInterface::GUEST_ROLE_NAME,
            ],

            self::ACTION_READ => [
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],

            self::ACTION_UPDATE => [
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],

            self::ACTION_DELETE => [
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],

            self::ACTION_SEARCH => [
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_LIST_RESOLVED => [
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_LIST_UNRESOLVED => [
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_RESOLVE => [
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_IGNORE => [
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_DELETE => [
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_TEST => [
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_THROW => [
                RoleInterface::DEVELOPER_ROLE_NAME,
            ],
        ];
    }

    protected function getActionsWithoutEntity(): array
    {
        return array_merge(parent::getActionsWithoutEntity(), [
            self::PERMISSION_LIST_RESOLVED,
            self::PERMISSION_LIST_UNRESOLVED,
            self::PERMISSION_TEST,
            self::PERMISSION_THROW,
        ]);
    }
}
