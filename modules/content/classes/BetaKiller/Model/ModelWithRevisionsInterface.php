<?php
namespace BetaKiller\Model;

interface ModelWithRevisionsInterface
{
    public const ALL_REVISIONS_KEY   = 'all_revisions';
    public const ACTUAL_REVISION_KEY = 'actual_revision';

    /**
     * @return void
     */
    public function useLatestRevision(): void;

    /**
     * @return void
     */
    public function useActualRevision(): void;

    /**
     * @param \BetaKiller\Model\RevisionModelInterface $revision
     *
     * @return void
     */
    public function useRevision(RevisionModelInterface $revision): void;

    /**
     * @return void
     */
    public function setLatestRevisionAsActual(): void;

    /**
     * @return \BetaKiller\Model\RevisionModelInterface[]
     * @deprecated Move to Repository
     */
    public function getAllRevisions(): array;

    /**
     * @return bool
     */
    public function hasActualRevision(): bool;

    /**
     * @return $this
     * @deprecated Move to Repository
     */
    public function filterHavingActualRevision();

    /**
     * @return bool
     */
    public function isRevisionDataChanged(): bool;

    /**
     * @return bool
     * @deprecated Move to Repository
     */
    public function hasPendingRevisions(): bool;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     */
    public function injectNewRevisionAuthor(UserInterface $user): void;
}
