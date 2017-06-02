<?php
namespace BetaKiller\Acl\Resource;

use Spotman\Acl\Resource\AbstractResolvingResource;

abstract class AbstractCrudlsPermissionsResource extends AbstractResolvingResource implements CrudlsPermissionsResourceInterface
{
    /**
     * @return bool
     */
    public function isCreateAllowed(): bool
    {
        return $this->isPermissionAllowed(self::ACTION_CREATE);
    }

    /**
     * @return bool
     */
    public function isReadAllowed(): bool
    {
        return $this->isPermissionAllowed(self::ACTION_READ);
    }

    /**
     * @return bool
     */
    public function isUpdateAllowed(): bool
    {
        return $this->isPermissionAllowed(self::ACTION_UPDATE);
    }

    /**
     * @return bool
     */
    public function isDeleteAllowed(): bool
    {
        return $this->isPermissionAllowed(self::ACTION_DELETE);
    }

    /**
     * @return bool
     */
    public function isListAllowed(): bool
    {
        return $this->isPermissionAllowed(self::ACTION_LIST);
    }

    /**
     * @return bool
     */
    public function isSearchAllowed(): bool
    {
        return $this->isPermissionAllowed(self::ACTION_SEARCH);
    }
}
