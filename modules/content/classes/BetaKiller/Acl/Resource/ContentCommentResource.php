<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\Role;

class ContentCommentResource extends AbstractStatusRelatedEntityAclResource
{
    /**
     * Provides array of roles` names which are allowed to create entities
     *
     * @return string[]
     */
    protected function getCreatePermissionRoles()
    {
        return [
            Role::GUEST_ROLE_NAME,
        ];
    }

    /**
     * Provides array of roles` names which are allowed to browse(list) entities
     *
     * @return string[]
     */
    protected function getListPermissionRoles()
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
    protected function getSearchPermissionRoles()
    {
        return [
            Role::MODERATOR_ROLE_NAME,
        ];
    }
}
