<?php
namespace BetaKiller\Content;

use Model_ContentEntity;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

interface ContentRelatedInterface extends OrmInterface
{
    /**
     * @param Model_ContentEntity $entity
     *
     * @return $this
     */
    public function set_entity(Model_ContentEntity $entity);

    /**
     * @return Model_ContentEntity
     */
    public function get_entity();

    /**
     * @return string
     */
    public function get_entity_slug();

    /**
     * Устанавливает ссылку на ID записи из таблицы, к которой привязана entity
     *
     * @param int $id
     *
     * @return $this
     */
    public function set_entity_item_id($id);

    /**
     * @return int
     */
    public function get_entity_item_id();

    /**
     * @param $item_id
     *
     * @return $this
     */
    public function filter_entity_item_id($item_id);

    /**
     * @param int[] $item_ids
     *
     * @return $this
     */
    public function filter_entity_item_ids(array $item_ids);

    /**
     * @param int $entity_id
     *
     * @return $this
     */
    public function filter_entity_id($entity_id);

    /**
     * @return $this
     */
    public function group_by_entity_item_id();

    /**
     * @param Model_ContentEntity $entity
     *
     * @return int[]
     */
    public function get_entity_items_ids(Model_ContentEntity $entity);
}
