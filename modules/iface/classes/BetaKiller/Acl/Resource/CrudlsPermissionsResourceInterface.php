<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\IFace\CrudlsActionsInterface;
use Spotman\Acl\Resource\ResolvingResourceInterface;

interface CrudlsPermissionsResourceInterface extends ResolvingResourceInterface, CrudlsActionsInterface
{
    /**
     * @return bool
     */
    public function isListAllowed(): bool;

    /**
     * @return bool
     */
    public function isCreateAllowed(): bool;

    /**
     * @return bool
     */
    public function isReadAllowed(): bool;

    /**
     * @return bool
     */
    public function isUpdateAllowed(): bool;

    /**
     * @return bool
     */
    public function isDeleteAllowed(): bool;

    /**
     * @return bool
     */
    public function isSearchAllowed(): bool;
}
