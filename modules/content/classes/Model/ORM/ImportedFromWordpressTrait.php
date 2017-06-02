<?php

trait Model_ORM_ImportedFromWordpressTrait
{
    /**
     * @param int $value
     * @return $this|ORM
     */
    public function set_wp_id($value)
    {
        return $this->set('wp_id', (int) $value);
    }

    /**
     * @return int|null
     */
    public function get_wp_id()
    {
        return $this->get('wp_id');
    }

    /**
     * @param $wp_id
     * @return $this
     */
    public function filter_wp_id($wp_id)
    {
        return $this->where($this->object_column('wp_id'), '=', $wp_id);
    }

    /**
     * @param int $id
     * @return \BetaKiller\Content\ImportedFromWordpressInterface|$this|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    public function find_by_wp_id($id)
    {
        /** @var \BetaKiller\Utils\Kohana\ORM\OrmInterface|$this|\BetaKiller\Content\ImportedFromWordpressInterface $orm */
        $orm = $this->model_factory();

        /** @var \BetaKiller\Utils\Kohana\ORM\OrmInterface|$this|\BetaKiller\Content\ImportedFromWordpressInterface $model */
        $model = $orm->filter_wp_id($id)->find();

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
    public function find_ids_by_wp_ids($wp_ids)
    {
        /** @var \ORM|$this $model */
        $model = $this->model_factory();

        $model->order_by_wp_ids($wp_ids);

        return $model
            ->where($this->object_column('wp_id'), 'IN', (array) $wp_ids)
            ->find_all()
            ->as_array(NULL, $this->primary_key());
    }

    /**
     * @param array $wp_ids
     * @return \BetaKiller\Utils\Kohana\ORM\OrmInterface|$this
     */
    public function order_by_wp_ids(array $wp_ids)
    {
        return $this->order_by_field_sequence($this->object_column('wp_id'), $wp_ids);
    }
}
