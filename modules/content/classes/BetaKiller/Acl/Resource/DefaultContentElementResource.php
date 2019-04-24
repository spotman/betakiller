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
            RoleInterface::MODERATOR,
        ];
    }

    /**
     * @return array
     */
    protected function getStoreDefaultAccessList(): array
    {
        return [
            Content::WRITER_ROLE_NAME,
            RoleInterface::MODERATOR,
        ];
    }

    /**
     * @return array
     */
    protected function getReadDefaultAccessList(): array
    {
        return [
            RoleInterface::GUEST,
            RoleInterface::LOGIN,
        ];
    }

    /**
     * @return array
     */
    protected function getUpdateDefaultAccessList(): array
    {
        return [
            Content::WRITER_ROLE_NAME,
            RoleInterface::MODERATOR,
        ];
    }

    /**
     * @return array
     */
    protected function getDeleteDefaultAccessList(): array
    {
        return [
            Content::WRITER_ROLE_NAME,
            RoleInterface::MODERATOR,
        ];
    }

    /**
     * @return array
     */
    protected function getListDefaultAccessList(): array
    {
        return [
            Content::WRITER_ROLE_NAME,
            RoleInterface::MODERATOR,
        ];
    }

    /**
     * @return array
     */
    protected function getSearchDefaultAccessList(): array
    {
        return [
            Content::WRITER_ROLE_NAME,
            RoleInterface::MODERATOR,
        ];
    }
}
