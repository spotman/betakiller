<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\Role;
use Spotman\Acl\Resource\AbstractResolvingResource;

class PhpExceptionResource extends AbstractResolvingResource
{
    const PERMISSION_RESOLVE = 'resolve';
    const PERMISSION_IGNORE  = 'ignore';
    const PERMISSION_DELETE  = 'delete';
    const PERMISSION_THROW   = 'throwHttpException';

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
            self::PERMISSION_RESOLVE => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_IGNORE => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_DELETE => [
                Role::DEVELOPER_ROLE_NAME,
            ],

            self::PERMISSION_THROW => [
                Role::DEVELOPER_ROLE_NAME,
            ],
        ];
    }
}
