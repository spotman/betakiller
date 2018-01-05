<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\Entity;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

trait OrmBasedEntityItemRelatedRepositoryTrait
{
//    public function get_entity_items_ids(Entity $entity)
//    {
//        /** @var EntityItemRelatedInterface $model */
//        $model = $this->model_factory();
//
//        return $model
//            ->filterEntityID($entity->get_id())
//            ->group_by_entity_item_id()
//            ->find_all()
//            ->as_array(null, 'entity_item_id');
//    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param int                                       $item_id
     */
    protected function filterEntityItemID(OrmInterface $orm, int $item_id): void
    {
        $orm->where($orm->object_column('entity_item_id'), '=', $item_id);
    }

//    /**
//     * @param array $item_ids
//     *
//     * @return EntityItemRelatedInterface|$this
//     */
//    public function filter_entity_item_ids(array $item_ids)
//    {
//        return $this->where($this->object_column('entity_item_id'), 'IN', $item_ids);
//    }

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

//    /**
//     * @return EntityItemRelatedInterface|$this
//     */
//    public function group_by_entity_item_id()
//    {
//        return $this->group_by($this->object_column('entity_item_id'));
//    }
}
