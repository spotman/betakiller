<?php
namespace BetaKiller\Acl\Resource;

use Spotman\Acl\Exception;
use BetaKiller\Model\AbstractEntityInterface;

abstract class AbstractEntityRelatedAclResource extends AbstractCrudlsPermissionsResource implements EntityRelatedAclResourceInterface
{
    /**
     * @var \BetaKiller\Model\AbstractEntityInterface
     */
    private $entity;

    /**
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     * @return $this
     */
    public function setEntity(AbstractEntityInterface $entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @return \BetaKiller\Model\AbstractEntityInterface
     * @throws \Spotman\Acl\Exception
     */
    protected function getEntity(): AbstractEntityInterface
    {
        if (!$this->entity) {
            throw new Exception('Entity model is missing, set it via setEntity() method');
        }

        return $this->entity;
    }

    public function isEntityRequiredForAction(string $actionName): bool
    {
        return !in_array($actionName, $this->getActionsWithoutEntity(), true);
    }

    protected function getActionsWithoutEntity(): array
    {
        // Create, list and search actions do not require entity model to be set before processing
        return [
            self::ACTION_CREATE,
            self::ACTION_LIST,
            self::ACTION_SEARCH,
        ];
    }
}
