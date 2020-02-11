<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\RoleInterface;

final class UserResource extends AbstractHasWorkflowStateAclResource
{
    /**
     * @inheritDoc
     */
    protected function getCreatePermissionRoles(): array
    {
        // No one can create via API
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function getListPermissionRoles(): array
    {
        return [
            RoleInterface::ROLE_USER_MANAGEMENT,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getSearchPermissionRoles(): array
    {
        return [
            RoleInterface::ROLE_USER_MANAGEMENT,
        ];
    }
}
