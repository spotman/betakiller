<?php

use BetaKiller\Model\Entity;

trait Model_ORM_EntityRelatedModelTrait
{
    /**
     * @var \BetaKiller\Model\AbstractEntityInterface
     */
    protected $linkedModel;

    protected function initialize_entity_relation(): void
    {
        $this->belongs_to([
            'entity' => [
                'model'       => 'Entity',
                'foreign_key' => 'entity_id',
            ],
        ]);

        $this->load_with(['entity']);
    }

    /**
     * @param Entity $entity
     *
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_entity(Entity $entity)
    {
        return $this->set('entity', $entity);
    }

    /**
     * @return Entity
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
        return $this->get_entity()->getSlug();
    }

    /**
     * Set link to linked record ID
     *
     * @param int $id
     *
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function set_entity_item_id($id)
    {
        return $this->set('entity_item_id', (int)$id);
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
     * @return \BetaKiller\Model\DispatchableEntityInterface
     */
    protected function getRelatedEntityInstance()
    {
        if (!$this->linkedModel) {
            $id                = $this->get_entity_item_id();
            $this->linkedModel = $this->get_entity()->getLinkedEntityInstance($id);
        }

        return $this->linkedModel;
    }
}
