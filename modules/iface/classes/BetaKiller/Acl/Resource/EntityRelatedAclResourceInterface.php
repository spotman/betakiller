<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\AbstractEntityInterface;

interface EntityRelatedAclResourceInterface extends CrudlsPermissionsResourceInterface
{
    /**
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @return $this
     */
    public function setEntity(AbstractEntityInterface $entity): self;

    /**
     * @param string $actionName
     *
     * @return bool
     */
    public function isEntityRequiredForAction(string $actionName): bool;
}
