<?php

use BetaKiller\Content\ContentRelatedInterface;

trait Model_ORM_ContentRelatedTrait
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
     * Set link to linked record ID
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

    public function get_entity_items_ids(Model_ContentEntity $entity)
    {
        /** @var ContentRelatedInterface $model */
        $model = $this->model_factory();

        return $model
            ->filter_entity_id($entity->get_id())
            ->group_by_entity_item_id()
            ->find_all()
            ->as_array(NULL, 'entity_item_id');
    }

    /**
     * @param int $item_id
     *
     * @return ContentRelatedInterface|$this
     */
    public function filter_entity_item_id($item_id)
    {
        return $this->where($this->object_column('entity_item_id'), '=', $item_id);
    }

    /**
     * @param array $item_ids
     *
     * @return ContentRelatedInterface|$this
     */
    public function filter_entity_item_ids(array $item_ids)
    {
        return $this->where($this->object_column('entity_item_id'), 'IN', $item_ids);
    }

    /**
     * @param $entity_id
     *
     * @return ContentRelatedInterface|$this
     */
    public function filter_entity_id($entity_id)
    {
        return $this->where($this->object_column('entity_id'), '=', $entity_id);
    }

    protected function filter_entity_and_entity_item_id(Model_ContentEntity $entity = null, $entity_item_id = null)
    {
        if ($entity) {
            $this->filter_entity_id($entity->get_id());
        }

        if ($entity_item_id) {
            $this->filter_entity_item_id($entity_item_id);
        }

        return $this;
    }

    /**
     * @return ContentRelatedInterface|$this
     */
    public function group_by_entity_item_id()
    {
        return $this->group_by($this->object_column('entity_item_id'));
    }

    /**
     * @return \BetaKiller\Content\LinkedContentModelInterface
     */
    protected function get_related_item_model()
    {
        $id = $this->get_entity_item_id();
        return $this->get_entity()->get_linked_model_instance($id);
    }
}
