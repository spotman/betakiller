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
