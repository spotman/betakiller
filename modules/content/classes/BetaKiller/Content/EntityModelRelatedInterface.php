<?php
namespace BetaKiller\Content;

use BetaKiller\Model\Entity;

interface EntityModelRelatedInterface
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
}
