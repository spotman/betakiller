<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\IFace\CrudlsActionsInterface;
use Spotman\Acl\Resource\ResolvingResourceInterface;

interface CrudlsPermissionsResourceInterface extends ResolvingResourceInterface, CrudlsActionsInterface
{
    /**
     * @return bool
     */
    public function isListAllowed();

    /**
     * @return bool
     */
    public function isCreateAllowed();

    /**
     * @return bool
     */
    public function isReadAllowed();

    /**
     * @return bool
     */
    public function isUpdateAllowed();

    /**
     * @return bool
     */
    public function isDeleteAllowed();

    /**
     * @return bool
     */
    public function isSearchAllowed();
}
