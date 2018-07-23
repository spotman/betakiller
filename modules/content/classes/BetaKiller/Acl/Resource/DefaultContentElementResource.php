<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Content\Content;
use BetaKiller\Model\RoleInterface;

class DefaultContentElementResource extends AbstractAssetsAclResource
{
    /**
     * @return array
     */
    protected function getUploadDefaultAccessList(): array
    {
        return [
            Content::WRITER_ROLE_NAME,
            RoleInterface::MODERATOR_ROLE_NAME,
            RoleInterface::ADMIN_ROLE_NAME,
        ];
    }

    /**
     * @return array
     */
    protected function getCreateDefaultAccessList(): array
    {
        return [
            Content::WRITER_ROLE_NAME,
            RoleInterface::MODERATOR_ROLE_NAME,
            RoleInterface::ADMIN_ROLE_NAME,
        ];
    }

    /**
     * @return array
     */
    protected function getReadDefaultAccessList(): array
    {
        return [
            RoleInterface::GUEST_ROLE_NAME,
            RoleInterface::LOGIN_ROLE_NAME,
        ];
    }

    /**
     * @return array
     */
    protected function getUpdateDefaultAccessList(): array
    {
        return [
            Content::WRITER_ROLE_NAME,
            RoleInterface::MODERATOR_ROLE_NAME,
            RoleInterface::ADMIN_ROLE_NAME,
        ];
    }

    /**
     * @return array
     */
    protected function getDeleteDefaultAccessList(): array
    {
        return [
            Content::WRITER_ROLE_NAME,
            RoleInterface::MODERATOR_ROLE_NAME,
            RoleInterface::ADMIN_ROLE_NAME,
        ];
    }

    /**
     * @return array
     */
    protected function getListDefaultAccessList(): array
    {
        return [
            Content::WRITER_ROLE_NAME,
            RoleInterface::MODERATOR_ROLE_NAME,
            RoleInterface::ADMIN_ROLE_NAME,
        ];
    }

    /**
     * @return array
     */
    protected function getSearchDefaultAccessList(): array
    {
        return [
            Content::WRITER_ROLE_NAME,
            RoleInterface::MODERATOR_ROLE_NAME,
            RoleInterface::ADMIN_ROLE_NAME,
        ];
    }
}
