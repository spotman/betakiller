<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Model\AbstractEntityInterface;
use Spotman\Acl\AclException;

abstract class AbstractEntityRelatedAclResource extends AbstractCrudlsPermissionsResource implements
    EntityRelatedAclResourceInterface
{
    /**
     * @var \BetaKiller\Model\AbstractEntityInterface
     */
    private $entity;

    /**
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     *
     * @return $this
     */
    public function setEntity(AbstractEntityInterface $entity): EntityRelatedAclResourceInterface
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return \BetaKiller\Model\AbstractEntityInterface
     * @throws \Spotman\Acl\AclException
     */
    protected function getEntity(): AbstractEntityInterface
    {
        if (!$this->entity) {
            throw new AclException('Entity model is missing, set it via setEntity() method');
        }

        return $this->entity;
    }

    public function isEntityRequiredForAction(string $actionName): bool
    {
        return !\in_array($actionName, $this->getActionsWithoutEntity(), true);
    }

    protected function getActionsWithoutEntity(): array
    {
        // Create, list and search actions do not require entity model to be set before processing
        return self::ACTIONS_WITHOUT_ENTITY;
    }
}
