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

    protected function initializeEntityRelation(): void
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
     * @param \BetaKiller\Model\EntityModelInterface $entity
     *
     * @return EntityItemRelatedInterface
     */
    public function setEntity(EntityModelInterface $entity): EntityItemRelatedInterface
    {
        return $this->set('entity', $entity);
    }

    /**
     * @return \BetaKiller\Model\EntityModelInterface
     */
    public function getEntity(): EntityModelInterface
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
     * @return \BetaKiller\Model\RelatedEntityInterface
     */
    protected function getRelatedEntityInstance(): RelatedEntityInterface
    {
        if (!$this->linkedModel) {
            $id                = $this->getEntityItemID();
            $this->linkedModel = $this->getEntity()->getLinkedEntityInstance($id);
        }

        return $this->linkedModel;
    }
}
