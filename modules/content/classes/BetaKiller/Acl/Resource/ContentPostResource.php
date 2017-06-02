<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\Role;

class ContentPostResource extends AbstractStatusRelatedEntityAclResource
{
    const ACTION_PREVIEW = 'preview';

    /**
     * Returns default permissions bundled with current resource
     * Key=>Value pairs where key is a permission identity and value is an array of roles
     * Useful for presetting permissions for resources with fixed access control list or permissions based on hard-coded logic
     *
     * @return string[][]
     */
    public function getDefaultAccessList(): array
    {
        return parent::getDefaultAccessList() + [
            self::ACTION_PREVIEW => [
                Role::WRITER_ROLE_NAME,
            ]
        ];
    }

    /**
     * Provides array of roles` names which are allowed to create entities
     *
     * @return array
     */
    protected function getCreatePermissionRoles(): array
    {
        return [
            Role::ADMIN_ROLE_NAME,
            Role::MODERATOR_ROLE_NAME,
            Role::DEVELOPER_ROLE_NAME,
            Role::WRITER_ROLE_NAME,
        ];
    }

    /**
     * Provides array of roles` names which are allowed to browse(list) entities
     *
     * @return string[]
     */
    protected function getListPermissionRoles(): array
    {
        return [
            Role::GUEST_ROLE_NAME,
        ];
    }

    /**
     * Provides array of roles` names which are allowed to search for entities
     *
     * @return string[]
     */
    protected function getSearchPermissionRoles(): array
    {
        return [
            Role::GUEST_ROLE_NAME,
        ];
    }
}
