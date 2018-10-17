<?php
namespace BetaKiller\Model;

interface RevisionModelInterface extends DispatchableEntityInterface
{
    /**
     * @return \BetaKiller\Model\RevisionModelInterface
     */
    public function getLatestRevision(): RevisionModelInterface;

    /**
     * @param string $column
     *
     * @return mixed
     */
    public function get($column);

    /**
     * @param string $column
     * @param mixed $value
     *
     * @return $this
     */
    public function set($column, $value);

    public function setCreatedBy(UserInterface $model);

    /**
     * @return UserInterface
     */
    public function getCreatedBy(): UserInterface;

    public function setCreatedAt(\DateTime $time);

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTimeInterface;

    /**
     * @return \BetaKiller\Model\AbstractEntityInterface
     */
    public function getRevisionRelatedEntity(): AbstractEntityInterface;

    /**
     * @param \BetaKiller\Model\AbstractEntityInterface $entity
     */
    public function setRelatedEntity(AbstractEntityInterface $entity): void;

    /**
     * @return bool
     */
    public function isChanged(): bool;

    /**
     * Returns new revision model if new revision was created or null if not
     *
     * @return \BetaKiller\Model\RevisionModelInterface|null
     */
    public function createNewRevisionIfChanged(): ?RevisionModelInterface;

    /**
     * @param \BetaKiller\Model\RevisionModelInterface $actual
     *
     * @return $this
     */
    public function filterPending(RevisionModelInterface $actual);
}
