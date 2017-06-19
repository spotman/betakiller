<?php
namespace BetaKiller\Model;

interface ModelWithRevisionsInterface
{
// TODO copy methods from abstract class

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
