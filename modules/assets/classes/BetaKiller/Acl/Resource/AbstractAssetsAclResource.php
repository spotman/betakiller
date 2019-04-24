<?php
namespace BetaKiller\Acl\Resource;

abstract class AbstractAssetsAclResource extends AbstractEntityRelatedAclResource implements AssetsAclResourceInterface
{
    /**
     * @return bool
     */
    public function isUploadAllowed(): bool
    {
        return $this->isPermissionAllowed(self::ACTION_UPLOAD);
    }

    /**
     * Returns default permissions bundled with current resource
     * Key=>Value pairs where key is a permission identity and value is an array of roles
     * Useful for presetting permissions for resources with fixed access control list or permissions based on hard-coded logic
     *
     * @return string[][]
     */
    public function getDefaultAccessList(): array
    {
        return [
            self::ACTION_UPLOAD => $this->getUploadDefaultAccessList(),
            self::ACTION_CREATE => $this->getStoreDefaultAccessList(),
            self::ACTION_READ   => $this->getReadDefaultAccessList(),
            self::ACTION_UPDATE => $this->getUpdateDefaultAccessList(),
            self::ACTION_DELETE => $this->getDeleteDefaultAccessList(),
            self::ACTION_LIST   => $this->getListDefaultAccessList(),
            self::ACTION_SEARCH => $this->getSearchDefaultAccessList(),
        ];
    }

    protected function getActionsWithoutEntity(): array
    {
        // Upload and store actions do not require assets model for access resolving
        return array_merge(parent::getActionsWithoutEntity(), [
            self::ACTION_UPLOAD,
        ]);
    }

    /**
     * @return array
     */
    abstract protected function getUploadDefaultAccessList(): array;

    /**
     * @return array
     */
    abstract protected function getStoreDefaultAccessList(): array;

    /**
     * @return array
     */
    abstract protected function getReadDefaultAccessList(): array;

    /**
     * @return array
     */
    abstract protected function getUpdateDefaultAccessList(): array;

    /**
     * @return array
     */
    abstract protected function getDeleteDefaultAccessList(): array;

    /**
     * @return array
     */
    abstract protected function getListDefaultAccessList(): array;

    /**
     * @return array
     */
    abstract protected function getSearchDefaultAccessList(): array;
}
