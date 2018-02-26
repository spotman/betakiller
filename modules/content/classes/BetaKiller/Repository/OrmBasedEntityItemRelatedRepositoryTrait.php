<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\Entity;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

trait OrmBasedEntityItemRelatedRepositoryTrait
{
    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param int                                       $itemID
     */
    protected function filterEntityItemID(OrmInterface $orm, ?int $itemID): void
    {
        if ($itemID === null) {
            $orm->where($orm->object_column('entity_item_id'), 'IS', null);
        } else {
            $orm->where($orm->object_column('entity_item_id'), '=', $itemID);
        }
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param int                                       $entityId
     */
    protected function filterEntityID(OrmInterface $orm, ?int $entityId): void
    {
        if ($entityId === null) {
            $orm->where($orm->object_column('entity_id'), 'is', null);
        } else {
            $orm->where($orm->object_column('entity_id'), '=', $entityId);
        }
    }

    protected function filterEntityAndEntityItemID(
        OrmInterface $orm,
        ?Entity $entity,
        ?int $entityItemId
    ): void {
        $this->filterEntityID($orm, $entity ? $entity->getID() : null);
        $this->filterEntityItemID($orm, $entityItemId);
    }

    protected function filterEntityOrEntityItemID(
        OrmInterface $orm,
        ?Entity $entity = null,
        ?int $entityItemId = null
    ): void {
        if ($entity) {
            $this->filterEntityID($orm, $entity->getID());
        }

        if ($entityItemId) {
            $this->filterEntityItemID($orm, $entityItemId);
        }
    }
}
