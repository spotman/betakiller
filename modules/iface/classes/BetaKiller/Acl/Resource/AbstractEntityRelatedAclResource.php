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

    public function setEntity(AbstractEntityInterface $entity)
    {
        $this->entity = $entity;
    }

    protected function getEntity()
    {
        if (!$this->entity) {
            throw new Exception('Entity model is missing, set it via setEntity() method');
        }

        return $this->entity;
    }
}
