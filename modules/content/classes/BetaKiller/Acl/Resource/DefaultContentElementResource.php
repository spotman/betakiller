<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\Role;

class DefaultContentElementResource extends AbstractAssetsAclResource
{
    /**
     * @return array
     */
    protected function getUploadDefaultAccessList(): array
    {
        return [
            Role::WRITER_ROLE_NAME,
            Role::MODERATOR_ROLE_NAME,
            Role::ADMIN_ROLE_NAME,
        ];
    }

    /**
     * @return array
     */
    protected function getCreateDefaultAccessList(): array
    {
        return [
            Role::WRITER_ROLE_NAME,
            Role::MODERATOR_ROLE_NAME,
            Role::ADMIN_ROLE_NAME,
        ];
    }

    /**
     * @return array
     */
    protected function getReadDefaultAccessList(): array
    {
        return [
            Role::GUEST_ROLE_NAME,
            Role::LOGIN_ROLE_NAME,
        ];
    }

    /**
     * @return array
     */
    protected function getUpdateDefaultAccessList(): array
    {
        return [
            Role::WRITER_ROLE_NAME,
            Role::MODERATOR_ROLE_NAME,
            Role::ADMIN_ROLE_NAME,
        ];
    }

    /**
     * @return array
     */
    protected function getDeleteDefaultAccessList(): array
    {
        return [
            Role::WRITER_ROLE_NAME,
            Role::MODERATOR_ROLE_NAME,
            Role::ADMIN_ROLE_NAME,
        ];
    }

    /**
     * @return array
     */
    protected function getListDefaultAccessList(): array
    {
        return [
            Role::WRITER_ROLE_NAME,
            Role::MODERATOR_ROLE_NAME,
            Role::ADMIN_ROLE_NAME,
        ];
    }

    /**
     * @return array
     */
    protected function getSearchDefaultAccessList(): array
    {
        return [
            Role::WRITER_ROLE_NAME,
            Role::MODERATOR_ROLE_NAME,
            Role::ADMIN_ROLE_NAME,
        ];
    }
}
