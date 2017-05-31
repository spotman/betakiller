<?php
namespace BetaKiller\Content;

use BetaKiller\Model\Entity;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

interface EntityRelatedInterface extends OrmInterface
{
    /**
     * @param Entity $entity
     *
     * @return $this
     */
    public function set_entity(Entity $entity);

    /**
     * @return Entity
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
     * @param Entity $entity
     *
     * @return int[]
     */
    public function get_entity_items_ids(Entity $entity);
}
