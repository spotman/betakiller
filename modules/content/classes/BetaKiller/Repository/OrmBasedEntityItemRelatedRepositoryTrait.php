<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\Entity;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

trait OrmBasedEntityItemRelatedRepositoryTrait
{
    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param int                                       $item_id
     */
    protected function filterEntityItemID(OrmInterface $orm, int $item_id): void
    {
        $orm->where($orm->object_column('entity_item_id'), '=', $item_id);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param int                                       $entity_id
     */
    protected function filterEntityID(OrmInterface $orm, int $entity_id): void
    {
        $orm->where($orm->object_column('entity_id'), '=', $entity_id);
    }

    protected function filterEntityAndEntityItemID(
        OrmInterface $orm,
        ?Entity $entity = null,
        ?int $entity_item_id = null
    ): void {
        if ($entity) {
            $this->filterEntityID($orm, $entity->getID());
        }

        if ($entity_item_id) {
            $this->filterEntityItemID($orm, $entity_item_id);
        }
    }
}
