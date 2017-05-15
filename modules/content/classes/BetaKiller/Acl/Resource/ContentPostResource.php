<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\Role;

class ContentPostResource extends AbstractStatusRelatedModelAclResource
{
    protected function getCreatePermissionRoles()
    {
        return [
            Role::ADMIN_ROLE_NAME,
            Role::MODERATOR_ROLE_NAME,
            Role::DEVELOPER_ROLE_NAME,
            Role::WRITER_ROLE_NAME,
        ];
    }

    /**
     * Returns true if this resource needs custom permission collector
     *
     * @return bool
     */
    public function isCustomPermissionCollectorUsed()
    {
        return true;
    }

//    /**
//     * Returns default permissions bundled with current resource
//     * Key=>Value pairs where key is a permission identity and value is an array of roles
//     * Useful for presetting permissions for resources with fixed access control list or permissions based on hard-coded logic
//     *
//     * @return string[][]
//     */
//    public function getDefaultAccessList()
//    {
//        return [
//            self::PERMISSION_CREATE =>  [
//                Role::ADMIN_ROLE_NAME,
//                Role::MODERATOR_ROLE_NAME,
//                Role::DEVELOPER_ROLE_NAME,
//                Role::WRITER_ROLE_NAME, 5
//            ],
//
//            self::PERMISSION_READ =>  [
//                Role::GUEST_ROLE_NAME, 6
//                Role::LOGIN_ROLE_NAME, 1
//            ],
//
//            self::PERMISSION_UPDATE =>  [
//                Role::ADMIN_ROLE_NAME, 2
//                Role::MODERATOR_ROLE_NAME, 4
//                Role::DEVELOPER_ROLE_NAME, 3
//            ],
//
//            self::PERMISSION_DELETE =>  [
//                Role::ADMIN_ROLE_NAME, 2
//                Role::DEVELOPER_ROLE_NAME, 3
//            ],
//        ];
//    }
}
