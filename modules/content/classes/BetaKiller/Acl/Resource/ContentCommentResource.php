<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Content\Content;
use BetaKiller\Model\RoleInterface;

final class ContentCommentResource extends AbstractHasWorkflowStateAclResource
{
    /**
     * Provides array of roles` names which are allowed to create entities
     *
     * @return string[]
     */
    protected function getCreatePermissionRoles(): array
    {
        return [
            RoleInterface::GUEST,
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
            RoleInterface::GUEST,
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
            Content::ROLE_CONTENT_MODERATOR,
        ];
    }
}
