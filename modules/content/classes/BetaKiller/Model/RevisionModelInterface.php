<?php
namespace BetaKiller\Model;

interface RevisionModelInterface
{
    public const ALL_REVISIONS_KEY   = 'all_revisions';
    public const ACTUAL_REVISION_KEY = 'actual_revision';

    public function getID();

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
     * @return bool
     */
    public function loaded();

    /**
     * @return bool
     */
    public function changed();

    /**
     * Returns new revision model if new revision was created or null if not
     *
     * @return \BetaKiller\Model\RevisionModelInterface|null
     */
    public function createNewRevisionIfChanged();

    /**
     * @param \BetaKiller\Model\RevisionModelInterface $actual
     *
     * @return $this
     */
    public function filterPending(RevisionModelInterface $actual);
}
