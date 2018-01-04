<?php
namespace BetaKiller\Model;

/**
 * Trait OrmBasedEntityItemRelatedModelTrait
 *
 * @package BetaKiller\Content
 */
trait OrmBasedEntityItemRelatedModelTrait
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
     * @return EntityItemRelatedInterface
     */
    public function setEntity(Entity $entity): EntityItemRelatedInterface
    {
        return $this->set('entity', $entity);
    }

    /**
     * @return Entity
     */
    public function getEntity(): Entity
    {
        return $this->get('entity');
    }

    /**
     * @return string
     * @throws \BetaKiller\Exception
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
     * @return EntityItemRelatedInterface
     */
    public function setEntityItemID(int $id): EntityItemRelatedInterface
    {
        return $this->set('entity_item_id', $id);
    }

    /**
     * @return int
     */
    public function getEntityItemID(): int
    {
        return $this->get('entity_item_id');
    }

    /**
     * @return \BetaKiller\Model\DispatchableEntityInterface
     * @throws \BetaKiller\Exception
     */
    protected function getRelatedEntityInstance(): DispatchableEntityInterface
    {
        if (!$this->linkedModel) {
            $id                = $this->getEntityItemID();
            $this->linkedModel = $this->getEntity()->getLinkedEntityInstance($id);
        }

        return $this->linkedModel;
    }
}
