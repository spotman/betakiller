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
     */
    public function setEntity(AbstractEntityInterface $entity): void
    {
        $this->entity = $entity;
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
}
