<?php
namespace BetaKiller\Model;

interface ModelWithRevisionsInterface
{
    /**
     * @return void
     */
    public function useLatestRevision(): void;

    /**
     * @return void
     */
    public function useActualRevision(): void;

    /**
     * @return void
     */
    public function setLatestRevisionAsActual(): void;

    /**
     * @return \BetaKiller\Model\RevisionModelInterface[]
     */
    public function getAllRevisions(): array;

    public function hasActualRevision(): bool;

    /**
     * @return $this
     */
    public function filterHavingActualRevision();

    /**
     * @return bool
     */
    public function isRevisionDataChanged(): bool;

    /**
     * @return bool
     */
    public function hasPendingRevisions(): bool;
}
