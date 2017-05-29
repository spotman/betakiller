<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\Role;
use Spotman\Acl\Resource\AbstractCrudPermissionsResource;
use Spotman\Acl\Resource\CrudPermissionsResourceInterface;

class ContentCommentResource extends AbstractCrudPermissionsResource
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
            self::PERMISSION_CREATE =>  [
                Role::GUEST_ROLE_NAME,
                Role::LOGIN_ROLE_NAME,
            ],

            self::PERMISSION_READ =>  [
                Role::GUEST_ROLE_NAME,
                Role::LOGIN_ROLE_NAME,
            ],

            self::PERMISSION_UPDATE =>  [
                Role::MODERATOR_ROLE_NAME,
            ],

            self::PERMISSION_DELETE =>  [
                Role::ADMIN_ROLE_NAME,
            ],
        ];
    }
}
