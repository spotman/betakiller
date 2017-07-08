<?php
namespace BetaKiller\Repository;

use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Trait OrmBasedRepositoryHasWordpressIdTrait
 *
 * @package BetaKiller\Content
 */
trait OrmBasedRepositoryHasWordpressIdTrait
{
    /**
     * @param int $id
     *
     * @return \BetaKiller\Model\EntityHasWordpressIdInterface|mixed|null
     */
    public function findByWpID(int $id)
    {
        /** @var OrmInterface $orm */
        $orm = $this->getOrmInstance();

        $this->filterWpID($orm, $id);

        $model = $orm->find();

        return $model->loaded() ? $model : null;
    }

    /**
     * Returns array of records IDs by their WP IDs
     *
     * @param int|array $wp_ids
     *
     * @return array
     */
    public function findIDsByWpIDs(array $wp_ids): array
    {
        /** @var OrmInterface $orm */
        $orm = $this->getOrmInstance();

        $this->orderByWpIDs($orm, $wp_ids);

        return $orm
            ->where($orm->object_column('wp_id'), 'IN', $wp_ids)
            ->find_all()
            ->as_array(null, $orm->primary_key());
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param int                                       $wp_id
     */
    private function filterWpID(OrmInterface $orm, int $wp_id): void
    {
        $orm->where($orm->object_column('wp_id'), '=', $wp_id);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param array                                     $wp_ids
     */
    private function orderByWpIDs(OrmInterface $orm, array $wp_ids): void
    {
        $orm->order_by_field_sequence($orm->object_column('wp_id'), $wp_ids);
    }
}
