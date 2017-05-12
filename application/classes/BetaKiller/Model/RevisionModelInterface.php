<?php
namespace BetaKiller\Model;

interface RevisionModelInterface
{
    const ALL_REVISIONS_KEY   = 'all_revisions';
    const ACTUAL_REVISION_KEY = 'actual_revision';

    /**
     * @return \BetaKiller\Model\RevisionModelInterface
     */
    public function getLatestRevision();

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
    public function getCreatedBy();

    public function setCreatedAt(\DateTime $time);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

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
}
