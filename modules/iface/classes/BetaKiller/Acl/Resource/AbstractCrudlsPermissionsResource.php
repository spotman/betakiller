<?php
namespace BetaKiller\Acl\Resource;

use Spotman\Acl\Resource\AbstractResolvingResource;

abstract class AbstractCrudlsPermissionsResource extends AbstractResolvingResource implements CrudlsPermissionsResourceInterface
{
    /**
     * @return bool
     */
    public function isCreateAllowed()
    {
        return $this->isPermissionAllowed(self::CREATE_ACTION);
    }

    /**
     * @return bool
     */
    public function isReadAllowed()
    {
        return $this->isPermissionAllowed(self::READ_ACTION);
    }

    /**
     * @return bool
     */
    public function isUpdateAllowed()
    {
        return $this->isPermissionAllowed(self::UPDATE_ACTION);
    }

    /**
     * @return bool
     */
    public function isDeleteAllowed()
    {
        return $this->isPermissionAllowed(self::DELETE_ACTION);
    }

    /**
     * @return bool
     */
    public function isListAllowed()
    {
        return $this->isPermissionAllowed(self::LIST_ACTION);
    }

    /**
     * @return bool
     */
    public function isSearchAllowed()
    {
        return $this->isPermissionAllowed(self::SEARCH_ACTION);
    }
}
