<?php
namespace BetaKiller\Model;

use BetaKiller\Exception;
use DateTime;

abstract class AbstractOrmBasedModelWithRevisions extends \ORM implements ModelWithRevisionsInterface
{
    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $revisionAuthor;

    /**
     * @var \BetaKiller\Model\RevisionModelInterface
     */
    private $currentRevision;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     */
    protected function initializeRevisionsRelations(): void
    {
        $this->belongs_to([
            ModelWithRevisionsInterface::ACTUAL_REVISION_KEY => [
                'model'       => $this->getRevisionModelName(),
                'foreign_key' => $this->getRelatedModelRevisionForeignKey(),
            ],
        ]);

        $this->has_many([
            ModelWithRevisionsInterface::ALL_REVISIONS_KEY => [
                'model'       => $this->getRevisionModelName(),
                'foreign_key' => $this->getRevisionModelForeignKey(),
            ],
        ]);

        // Autoload actual revision model
        $this->load_with([ModelWithRevisionsInterface::ACTUAL_REVISION_KEY]);
    }

    /**
     * Handles getting of column
     * Override this method to add custom get behavior
     *
     * @param   string $column Column name
     *
     * @return $this|\BetaKiller\Model\ExtendedOrmInterface
     * @throws \Kohana_Exception
     */
    public function get($column)
    {
        if ($this->isRevisionableColumn($column)) {
            return $this->getCurrentRevision()->get($column);
        }

        return parent::get($column);
    }

    /**
     * Handles setting of columns
     * Override this method to add custom set behavior
     *
     * @param  string $column Column name
     * @param  mixed  $value  Column value
     *
     * @return $this|\BetaKiller\Model\ExtendedOrmInterface
     * @throws \Kohana_Exception
     */
    public function set($column, $value)
    {
        if ($this->isRevisionableColumn($column)) {
            $revision = $this->getCurrentRevision();

            if ($revision->get($column) !== $value) {
                $revision->set($column, $value);
            }

            return $this;
        }

        return parent::set($column, $value);
    }

    /**
     * @return void
     */
    public function useLatestRevision(): void
    {
        $revision = $this->getLatestRevision();
        $this->setCurrentRevision($revision);
    }

    /**
     * @return void
     */
    public function useActualRevision(): void
    {
        $this->setCurrentRevision($this->getActualRevision());
    }

    /**
     * @param \BetaKiller\Model\RevisionModelInterface $revision
     *
     * @return void
     */
    public function useRevision(RevisionModelInterface $revision): void
    {
        $this->setCurrentRevision($revision);
    }

    /**
     * @return void
     */
    public function setLatestRevisionAsActual(): void
    {
        // Push changes to database so current revision becomes latest
        $this->createRevisionIfChanged();

        $this->currentRevision = $this->getLatestRevision();
        $this->setActualRevision($this->currentRevision);
    }

    public function isActualRevision(RevisionModelInterface $revision): bool
    {
        $actual = $this->getActualRevision();

        return $actual && $actual->getID() === $revision->getID();
    }

    /**
     * @return \BetaKiller\Model\RevisionModelInterface[]
     */
    public function getAllRevisions(): array
    {
        return $this->getAllRevisionsRelation()->orderByCreatedAt()->get_all();
    }

    /**
     * @return $this
     */
    public function filterHavingActualRevision()
    {
        $column = $this->object_column($this->getRelatedModelRevisionForeignKey());

        return $this->where($column, 'IS NOT', null);
    }

    public function hasActualRevision(): bool
    {
        return (bool)$this->getActualRevision();
    }

    /**
     * @return bool
     */
    public function hasPendingRevisions(): bool
    {
        $actual = $this->getActualRevision();

        // No actual revision needs to apply pending revisions
        if (!$actual) {
            return true;
        }

        return $this->getPendingCount($actual) > 0;
    }

    private function getPendingCount(RevisionModelInterface $actual): int
    {
        $orm = $this->getAllRevisionsRelation();

        if ($actual) {
            $orm->filterPending($actual);
        }

        return $orm->count_all();
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     */
    public function injectNewRevisionAuthor(UserInterface $user): void
    {
        $this->revisionAuthor = $user;
    }

    /**
     * Insert a new object to the database
     *
     * @param  \Validation $validation Validation object
     *
     * @return $this|\BetaKiller\Model\ExtendedOrmInterface
     * @throws \Kohana_Exception
     */
    public function create(\Validation $validation = null)
    {
        // First, create model and get ID
        $result = parent::create($validation);

        // Create revision linked to new model ID
        $newRevision = $this->createRevisionIfChanged();

        if ($newRevision) {
            $this->currentRevision = $newRevision;
        }

        // If new actual revision was set, then update
        if ($this->changed()) {
            parent::update();
        }

        return $result;
    }

    /**
     * Updates a single record or multiple records
     *
     * @chainable
     *
     * @param  \Validation $validation Validation object
     *
     * @return $this|\BetaKiller\Model\ExtendedOrmInterface
     * @throws \BetaKiller\Exception
     * @throws \Kohana_Exception
     */
    public function update(\Validation $validation = null)
    {
        // Update revision model first and set actual_revision field
        $newRevision = $this->createRevisionIfChanged();

        if ($newRevision) {
            $this->currentRevision = $newRevision;
        }

        // Regular update + store new revision ID
        return parent::update($validation);
    }

    /**
     * @return \BetaKiller\Model\RevisionModelInterface|null
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     */
    protected function createRevisionIfChanged(): ?RevisionModelInterface
    {
        $revision = $this->getCurrentRevision();

        // TODO разобраться с changed() (всегда true при сохранении поста так как формально данные были обновлены через set(), но по факту имели те же значения)

        if (!$revision->isChanged()) {
            return null;
        }

        if (!$this->hasID()) {
            throw new Exception('Can not link revision to related model without ID');
        }

        $user = $this->revisionAuthor;

        if (!$user) {
            throw new Exception('Inject revision author into model before saving');
        }

        $user->forceAuthorization();

        $revision->setCreatedAt(new DateTime);
        $revision->setCreatedBy($user);

        // Link new revision to current entity
        $revision->setRelatedEntity($this);

        $newRevision = $revision->createNewRevisionIfChanged();

        // Link latest revision to current post
        if ($newRevision && !$this->getActualRevision()) {
            $this->setActualRevision($newRevision);
        }

        return $newRevision;
    }

    public function isRevisionDataChanged(): bool
    {
        return $this->getCurrentRevision()->isChanged();
    }

    private function isRevisionableColumn(string $column): bool
    {
        return \in_array($column, $this->getFieldsWithRevisions(), true);
    }

    private function getCurrentRevision(): RevisionModelInterface
    {
        if (!$this->currentRevision) {
            // Use actual revision by default
            $this->currentRevision = $this->getActualRevision() ?: $this->getEmptyRevision();
        }

        return $this->currentRevision;
    }

    /**
     * @param \BetaKiller\Model\RevisionModelInterface $model
     *
     * @return void
     */
    private function setCurrentRevision(RevisionModelInterface $model): void
    {
        $this->currentRevision = $model;
    }

    /**
     * @return \BetaKiller\Model\RevisionModelInterface|null
     */
    private function getActualRevision(): ?RevisionModelInterface
    {
        /** @var \BetaKiller\Model\RevisionModelInterface $model */
        $model = $this->get(ModelWithRevisionsInterface::ACTUAL_REVISION_KEY);

        return $model->hasID() ? $model : null;
    }

    /**
     * @param \BetaKiller\Model\RevisionModelInterface $model
     *
     * @return void
     */
    private function setActualRevision(RevisionModelInterface $model): void
    {
        $this->set(ModelWithRevisionsInterface::ACTUAL_REVISION_KEY, $model);
    }

    private function getLatestRevision(): RevisionModelInterface
    {
        return $this->getAllRevisionsRelation()->getLatestRevision();
    }

    /**
     * @return \BetaKiller\Model\AbstractRevisionOrmModel
     */
    private function getAllRevisionsRelation(): AbstractRevisionOrmModel
    {
        return $this->get(ModelWithRevisionsInterface::ALL_REVISIONS_KEY);
    }

    /**
     * @return \BetaKiller\Model\RevisionModelInterface
     */
    private function getEmptyRevision(): RevisionModelInterface
    {
        return $this->model_factory(null, $this->getRevisionModelName());
    }

    /**
     * @return string
     */
    abstract protected function getRevisionModelName(): string;

    /**
     * @return string
     */
    abstract protected function getRelatedModelRevisionForeignKey(): string;

    /**
     * @return string
     */
    abstract protected function getRevisionModelForeignKey(): string;

    /**
     * @return string[]
     */
    abstract protected function getFieldsWithRevisions(): array;
}
