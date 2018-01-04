<?php
namespace BetaKiller\Model;

interface EntityItemRelatedInterface
{
    /**
     * @param Entity $entity
     *
     * @return \BetaKiller\Model\EntityItemRelatedInterface
     */
    public function setEntity(Entity $entity): EntityItemRelatedInterface;

    /**
     * @return Entity
     */
    public function getEntity(): Entity;

    /**
     * @return string
     */
    public function getEntitySlug(): string;

    /**
     * Устанавливает ссылку на ID записи из таблицы, к которой привязана entity
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
}
