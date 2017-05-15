<?php
namespace BetaKiller\Model;

use BetaKiller\Exception;
use BetaKiller\Helper\CurrentUserTrait;
use DateTime;

trait ModelWithRevisionsOrmTrait
{
    use CurrentUserTrait;

    /**
     * @var \BetaKiller\Model\RevisionModelInterface
     */
    private $currentRevision;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function initializeRevisionsRelations()
    {
        $this->belongs_to([
            RevisionModelInterface::ACTUAL_REVISION_KEY => [
                'model'       => $this->getRevisionModelName(),
                'foreign_key' => $this->getRelatedModelRevisionForeignKey(),
            ],
        ]);

        $this->has_many([
            RevisionModelInterface::ALL_REVISIONS_KEY => [
                'model'       => $this->getRevisionModelName(),
                'foreign_key' => $this->getRevisionModelForeignKey(),
            ],
        ]);

        // Autoload actual revision model
        $this->load_with([RevisionModelInterface::ACTUAL_REVISION_KEY]);
    }

    /**
     * Handles getting of column
     * Override this method to add custom get behavior
     *
     * @param   string $column Column name
     *
     * @throws \Kohana_Exception
     * @return mixed
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
     * @throws \Kohana_Exception
     * @return $this
     */
    public function set($column, $value)
    {
        if ($this->isRevisionableColumn($column)) {
            $this->getCurrentRevision()->set($column, $value);
            return $this;
        }

        return parent::set($column, $value);
    }

    /**
     * @return void
     */
    public function useLatestRevision()
    {
        $revision = $this->getLatestRevision();
        $this->setCurrentRevision($revision);
    }

    /**
     * @return void
     */
    public function useActualRevision()
    {
        $this->setCurrentRevision($this->getActualRevision());
    }

    /**
     * @return void
     */
    public function setLatestRevisionAsActual()
    {
        $revision = $this->getLatestRevision();
        $this->setActualRevision($revision);
    }

    /**
     * @return \BetaKiller\Model\RevisionModelInterface[]
     */
    public function getAllRevisions()
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

    /**
     * Insert a new object to the database
     * @param  \Validation $validation Validation object
     * @throws \Kohana_Exception
     * @return $this
     */
    public function create(\Validation $validation = NULL)
    {
        $result = parent::create($validation);

        $this->createRevisionRelatedModel();

        return $result;
    }

    /**
     * Updates a single record or multiple records
     *
     * @chainable
     * @param  \Validation $validation Validation object
     * @throws \Kohana_Exception
     * @return $this
     */
    public function update(\Validation $validation = NULL)
    {
        $result = parent::update($validation);

        $this->updateRevisionRelatedModel();

        return $result;
    }

    protected function createRevisionRelatedModel()
    {
        // Create revision if revisionable fields were set
        $this->createRevisionIfChanged();
    }

    /**
     * @return \BetaKiller\Model\RevisionModelInterface|null
     */
    protected function createRevisionIfChanged()
    {
        $revision = $this->getCurrentRevision();

        // TODO разобраться с changed() (всегда true при сохранении поста так как формально данные были обновлены через set(), но по факту имели те же значения)

        if (!$revision->changed()) {
            return null;
        }

        $id = $this->get_id();

        if (!$id) {
            throw new Exception('Can not link revision to related model without ID');
        }

        $revision->setCreatedAt(new DateTime);
        $revision->setCreatedBy($this->current_user());

        // Link new revision to current post
        $key = $this->getRevisionModelForeignKey();
        $revision->set($key, $id);

        $newRevision = $revision->createNewRevisionIfChanged();

        // Link latest revision to current post
        if ($newRevision && !$this->getActualRevision()) {
            $this->setActualRevision($newRevision);
            parent::update();
        }

        return $newRevision;
    }

    protected function updateRevisionRelatedModel()
    {
        $this->createRevisionIfChanged();
    }

    public function isRevisionDataChanged()
    {
        return $this->getCurrentRevision()->changed();
    }

    private function isRevisionableColumn($column)
    {
        return in_array($column, $this->getFieldsWithRevisions(), true);
    }

    private function getCurrentRevision()
    {
        if (!$this->currentRevision) {
            // Use actual revision by default
            $this->currentRevision = $this->getActualRevision() ?: $this->getEmptyRevision();
        }

        return $this->currentRevision;
    }

    /**
     * @param \BetaKiller\Model\RevisionModelInterface $model
     * @return void
     */
    private function setCurrentRevision(RevisionModelInterface $model)
    {
        $this->currentRevision = $model;
    }

    /**
     * @return \BetaKiller\Model\RevisionModelInterface|null
     */
    private function getActualRevision()
    {
        /** @var \BetaKiller\Model\RevisionModelInterface $model */
        $model = $this->get(RevisionModelInterface::ACTUAL_REVISION_KEY);

        return $model->loaded() ? $model : null;
    }

    /**
     * @param \BetaKiller\Model\RevisionModelInterface $model
     * @return void
     */
    private function setActualRevision(RevisionModelInterface $model)
    {
        $this->set(RevisionModelInterface::ACTUAL_REVISION_KEY, $model);
    }

    private function getLatestRevision()
    {
        return $this->getAllRevisionsRelation()->getLatestRevision();
    }

    /**
     * @return \BetaKiller\Model\AbstractRevisionOrmModel
     */
    private function getAllRevisionsRelation()
    {
        return $this->get(RevisionModelInterface::ALL_REVISIONS_KEY);
    }

    /**
     * @return \BetaKiller\Model\RevisionModelInterface
     */
    private function getEmptyRevision()
    {
        return $this->model_factory(null, $this->getRevisionModelName());
    }

    /**
     * @return string
     */
    abstract protected function getRevisionModelName();

    /**
     * @return string
     */
    abstract protected function getRelatedModelRevisionForeignKey();

    /**
     * @return string
     */
    abstract protected function getRevisionModelForeignKey();

    /**
     * @return string[]
     */
    abstract protected function getFieldsWithRevisions();
}
