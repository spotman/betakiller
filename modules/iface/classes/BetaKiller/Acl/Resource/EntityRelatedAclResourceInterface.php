<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\AbstractEntityInterface;

interface EntityRelatedAclResourceInterface extends CrudlsPermissionsResourceInterface
{
    public function setEntity(AbstractEntityInterface $entity);

    public function isEntityRequiredForAction(string $actionName): bool;
}
