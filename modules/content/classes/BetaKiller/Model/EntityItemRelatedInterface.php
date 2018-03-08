<?php
namespace BetaKiller\Model;

interface EntityItemRelatedInterface
{
    /**
     * @param \BetaKiller\Model\EntityModelInterface $entity
     *
     * @return \BetaKiller\Model\EntityItemRelatedInterface
     */
    public function setEntity(EntityModelInterface $entity): EntityItemRelatedInterface;

    /**
     * @return \BetaKiller\Model\EntityModelInterface
     */
    public function getEntity(): EntityModelInterface;

    /**
     * @return bool
     */
    public function hasEntity(): bool;

    /**
     * @return string
     */
    public function getEntitySlug(): string;

    /**
     * Set related record`s ID (from the entity-related table)
     *
     * @param int $id
     *
     * @return $this
     */
    public function setEntityItemID(int $id): EntityItemRelatedInterface;

    /**
     * @return int
     */
    public function getEntityItemID(): int;

    /**
     * @return bool
     */
    public function hasEntityItemID(): bool;
}
