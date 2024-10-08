<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Helper\ContentHelper;
use BetaKiller\Model\RoleInterface;

final class ContentPostResource extends AbstractHasWorkflowStateAclResource
{
    /**
     * Provides array of roles` names which are allowed to create entities
     *
     * @return array
     */
    protected function getCreatePermissionRoles(): array
    {
        return [
            ContentHelper::ROLE_CONTENT_MODERATOR,
            ContentHelper::ROLE_WRITER,
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
            RoleInterface::LOGIN,
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
            RoleInterface::GUEST,
            RoleInterface::LOGIN,
        ];
    }
}
