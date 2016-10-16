<?php

trait Model_ORM_ContentElementTrait
{
    protected function initialize_entity_relation()
    {
        $this->belongs_to([
            'entity'    =>  [
                'model'         =>  'ContentEntity',
                'foreign_key'   =>  'entity_id',
            ],
        ]);

        $this->load_with(['entity']);
    }

    /**
     * @param Model_ContentEntity $entity
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_entity(Model_ContentEntity $entity)
    {
        return $this->set('entity', $entity);
    }

    /**
     * @return Model_ContentEntity
     * @throws Kohana_Exception
     */
    public function get_entity()
    {
        return $this->get('entity');
    }

    /**
     * @return string
     */
    public function get_entity_slug()
    {
        return $this->get_entity()->get_slug();
    }

    /**
     * Устанавливает ссылку на ID записи из таблицы, к которой привязана entity
     *
     * @param int $id
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_entity_item_id($id)
    {
        return $this->set('entity_item_id', (int) $id);
    }

    /**
     * @return int
     * @throws Kohana_Exception
     */
    public function get_entity_item_id()
    {
        return $this->get('entity_item_id');
    }

    /**
     * @return Database_Result|Model_ContentElementInterface[]
     * @throws Kohana_Exception
     */
    public function get_all_files()
    {
        if (!$this->current_user(TRUE))
        {
            // Кешируем запрос для всех кроме админов
            $this->cached();
        }

        return $this->find_all();
    }

    public function get_entity_items_ids(Model_ContentEntity $entity)
    {
        /** @var Model_ContentElementInterface $model */
        $model = $this->model_factory();

        return $model
            ->filter_entity_id($entity->get_id())
            ->group_by_entity_item_id()
            ->find_all()
            ->as_array(NULL, 'entity_item_id');
    }

    /**
     * @param int $item_id
     * @return Model_ContentElementInterface
     */
    public function filter_entity_item_id($item_id)
    {
        return $this->where('entity_item_id', '=', $item_id);
    }

    /**
     * @param array $item_ids
     * @return Model_ContentElementInterface
     */
    public function filter_entity_item_ids(array $item_ids)
    {
        return $this->where('entity_item_id', 'IN', $item_ids);
    }

    /**
     * @param $entity_id
     * @return Model_ContentElementInterface
     */
    public function filter_entity_id($entity_id)
    {
        return $this->where('entity_id', '=', $entity_id);
    }

    /**
     * @return Model_ContentElementInterface
     */
    public function group_by_entity_item_id()
    {
        return $this->group_by('entity_item_id');
    }

    /**
     * Returns array representation of the model
     *
     * @return array
     */
    public function to_json()
    {
        return $this->as_array();
    }
}
