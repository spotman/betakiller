<?php

// Model_ContentImageElement extends Assets_Model_ORM_Image
// Model_ContentAttachmentElement extends Assets_Model_ORM
interface Model_ContentElementInterface
{
    /**
     * @param Model_ContentEntity $entity
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_entity(Model_ContentEntity $entity);

    /**
     * @return Model_ContentEntity
     * @throws Kohana_Exception
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
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_entity_item_id($id);

    /**
     * @return int
     * @throws Kohana_Exception
     */
    public function get_entity_item_id();

    /**
     * @return Database_Result|Model_ContentElementInterface[]
     * @throws Kohana_Exception
     */
    public function get_all_files();

    /**
     * @param Model_ContentEntity $entity
     * @return int[]
     */
    public function get_entity_items_ids(Model_ContentEntity $entity);

    /**
     * @param $item_id
     * @return $this
     */
    public function filter_entity_item_id($item_id);

    /**
     * @param array $item_ids
     * @return $this
     */
    public function filter_entity_item_ids(array $item_ids);

    /**
     * @param int $entity_id
     * @return $this
     */
    public function filter_entity_id($entity_id);

    /**
     * @return $this
     */
    public function group_by_entity_item_id();

    /**
     * Returns array representation of the model
     *
     * @return array
     */
    public function to_json();

}
