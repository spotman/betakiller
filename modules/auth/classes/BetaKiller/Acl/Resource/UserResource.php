<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\RoleInterface;

final class UserResource extends AbstractHasWorkflowStateAclResource
{
    public const ACTION_LOGIN       = 'login';
    public const ACTION_FORCE_LOGIN = 'force-login';

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
            RoleInterface::USER_MANAGEMENT,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getSearchPermissionRoles(): array
    {
        return [
            RoleInterface::USER_MANAGEMENT,
        ];
    }

    protected function getAdditionalAccessList(): array
    {
        return [
            self::ACTION_LOGIN => [
                RoleInterface::LOGIN,
            ],

            self::ACTION_FORCE_LOGIN => [
                RoleInterface::FORCE_LOGIN,
            ],
        ];
    }
}
