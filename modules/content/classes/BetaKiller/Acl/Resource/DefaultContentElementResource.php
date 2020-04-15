<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Helper\ContentHelper;
use BetaKiller\Model\RoleInterface;

final class DefaultContentElementResource extends AbstractAssetsAclResource
{
    /**
     * @return array
     */
    protected function getUploadDefaultAccessList(): array
    {
        return [
            ContentHelper::ROLE_WRITER,
            ContentHelper::ROLE_CONTENT_MODERATOR,
        ];
    }

    /**
     * @return array
     */
    protected function getStoreDefaultAccessList(): array
    {
        return [
            ContentHelper::ROLE_WRITER,
            ContentHelper::ROLE_CONTENT_MODERATOR,
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
            ContentHelper::ROLE_WRITER,
            ContentHelper::ROLE_CONTENT_MODERATOR,
        ];
    }

    /**
     * @return array
     */
    protected function getDeleteDefaultAccessList(): array
    {
        return [
            ContentHelper::ROLE_WRITER,
            ContentHelper::ROLE_CONTENT_MODERATOR,
        ];
    }

    /**
     * @return array
     */
    protected function getListDefaultAccessList(): array
    {
        return [
            ContentHelper::ROLE_WRITER,
            ContentHelper::ROLE_CONTENT_MODERATOR,
        ];
    }

    /**
     * @return array
     */
    protected function getSearchDefaultAccessList(): array
    {
        return [
            ContentHelper::ROLE_WRITER,
            ContentHelper::ROLE_CONTENT_MODERATOR,
        ];
    }
}
