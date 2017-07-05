<?php

use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use BetaKiller\Content\EntityHasWordpressIdInterface;

trait Model_ORM_RepositoryHasWordpressIdTrait
{
    /**
     * @param int $id
     *
     * @return \BetaKiller\Content\EntityHasWordpressIdInterface
     */
    public function find_by_wp_id(int $id): EntityHasWordpressIdInterface
    {
        /** @var OrmInterface $orm */
        $orm = $this->getOrmInstance();

        $this->filter_wp_id($orm, $id);

        /** @var \BetaKiller\Content\EntityHasWordpressIdInterface|OrmInterface $model */
        $model = $orm->find();

        if (!$model->loaded()) {
            $model->clear();
            $model->set_wp_id($id);
        }

        return $model;
    }

    /**
     * Returns array of records IDs by their WP IDs
     *
     * @param int|array $wp_ids
     * @return array
     */
    public function find_ids_by_wp_ids(array $wp_ids): array
    {
        /** @var OrmInterface $orm */
        $orm = $this->getOrmInstance();

        $this->order_by_wp_ids($orm, $wp_ids);

        return $orm
            ->where($orm->object_column('wp_id'), 'IN', $wp_ids)
            ->find_all()
            ->as_array(NULL, $orm->primary_key());
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param int                                       $wp_id
     */
    private function filter_wp_id(OrmInterface $orm, int $wp_id): void
    {
        $orm->where($orm->object_column('wp_id'), '=', $wp_id);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param array                                     $wp_ids
     */
    private function order_by_wp_ids(OrmInterface $orm, array $wp_ids): void
    {
        $orm->order_by_field_sequence($orm->object_column('wp_id'), $wp_ids);
    }
}
