<?php
namespace BetaKiller\Model;

use Kohana_Exception;
use ORM;

/**
 * Trait OrmBasedEntityRelatedModelTrait
 *
 * @package BetaKiller\Content
 */
trait OrmBasedEntityRelatedModelTrait
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
    public function setEntity(Entity $entity)
    {
        return $this->set('entity', $entity);
    }

    /**
     * @return Entity
     * @throws Kohana_Exception
     */
    public function getEntity(): Entity
    {
        return $this->get('entity');
    }

    /**
     * @return string
     */
    public function getEntitySlug(): string
    {
        return $this->getEntity()->getSlug();
    }

    /**
     * Set link to linked record ID
     *
     * @param int $id
     *
     * @return $this|ORM
     * @throws Kohana_Exception
     */
    public function setEntityItemID(int $id)
    {
        return $this->set('entity_item_id', $id);
    }

    /**
     * @return int
     * @throws Kohana_Exception
     */
    public function getEntityItemID(): int
    {
        return $this->get('entity_item_id');
    }

    /**
     * @return \BetaKiller\Model\DispatchableEntityInterface
     */
    protected function getRelatedEntityInstance()
    {
        if (!$this->linkedModel) {
            $id                = $this->getEntityItemID();
            $this->linkedModel = $this->getEntity()->getLinkedEntityInstance($id);
        }

        return $this->linkedModel;
    }
}
