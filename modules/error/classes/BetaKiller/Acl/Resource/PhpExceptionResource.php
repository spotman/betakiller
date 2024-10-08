<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\RoleInterface;

class PhpExceptionResource extends AbstractEntityRelatedAclResource
{
    public const PERMISSION_RESOLVE         = 'resolve';
    public const PERMISSION_IGNORE          = 'ignore';
    public const PERMISSION_TEST            = 'test';
    public const PERMISSION_THROW           = 'throwHttpException';

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
                RoleInterface::GUEST,
            ],

            self::ACTION_READ => [
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_UPDATE => [
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_DELETE => [
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_SEARCH => [
                RoleInterface::DEVELOPER,
            ],

            self::ACTION_LIST => [
                RoleInterface::DEVELOPER,
            ],

            self::PERMISSION_RESOLVE => [
                RoleInterface::DEVELOPER,
            ],

            self::PERMISSION_IGNORE => [
                RoleInterface::DEVELOPER,
            ],

            self::PERMISSION_TEST => [
                RoleInterface::DEVELOPER,
            ],

            self::PERMISSION_THROW => [
                RoleInterface::DEVELOPER,
            ],
        ];
    }

    protected function getActionsWithoutEntity(): array
    {
        return array_merge(parent::getActionsWithoutEntity(), [
            self::PERMISSION_TEST,
            self::PERMISSION_THROW,
        ]);
    }
}
