<?php
namespace BetaKiller\Model;

interface EntityModelRelatedInterface
{
    /**
     * @param Entity $entity
     *
     * @return $this
     */
    public function setEntity(Entity $entity);

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
    public function setEntityItemID(int $id);

    /**
     * @return int
     */
    public function getEntityItemID(): int;
}
