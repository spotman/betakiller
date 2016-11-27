<?php


namespace BetaKiller\Content;


interface ImportedFromWordpressInterface
{
    /**
     * @param int $value
     * @return $this|\ORM
     */
    public function set_wp_id($value);

    /**
     * @return int|null
     */
    public function get_wp_id();

    /**
     * @param $wp_id
     * @return $this
     */
    public function filter_wp_id($wp_id);

    /**
     * @param int $id
     * @return $this|\ORM|null
     */
    public function find_by_wp_id($id);

    /**
     * Returns array of records IDs by their WP IDs
     *
     * @param int|array $wp_ids
     * @return array
     */
    public function find_ids_by_wp_ids($wp_ids);

    /**
     * @param array $wp_ids
     * @return \ORM|$this
     */
    public function order_by_wp_ids(array $wp_ids);
}
