<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Content\Content;
use BetaKiller\Model\RoleInterface;

class ContentPostResource extends AbstractStatusRelatedEntityAclResource
{
    /**
     * Provides array of roles` names which are allowed to create entities
     *
     * @return array
     */
    protected function getCreatePermissionRoles(): array
    {
        return [
            RoleInterface::ADMIN_ROLE_NAME,
            RoleInterface::MODERATOR_ROLE_NAME,
            RoleInterface::DEVELOPER_ROLE_NAME,
            Content::WRITER_ROLE_NAME,
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
            RoleInterface::GUEST_ROLE_NAME,
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
            RoleInterface::GUEST_ROLE_NAME,
        ];
    }
}
