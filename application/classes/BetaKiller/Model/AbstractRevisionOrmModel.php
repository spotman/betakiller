<?php
namespace BetaKiller\Model;

abstract class AbstractRevisionOrmModel extends \ORM implements RevisionModelInterface
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->belongs_to([
            'owner'  =>  [
                'model' => 'User',
                'foreign_key' => 'created_by',
            ],
        ]);

        parent::_initialize();
    }

    /**
     * @return \BetaKiller\Model\RevisionModelInterface
     */
    public function getLatestRevision()
    {
        return $this->orderByCreatedAt()->find();
    }

    public function setCreatedBy(UserInterface $model)
    {
        return $this->set('owner', $model);
    }

    /**
     * @return \BetaKiller\Model\UserInterface
     */
    public function getCreatedBy()
    {
        return $this->get('owner');
    }

    public function setCreatedAt(\DateTime $time)
    {
        $this->set_datetime_column_value('created_at', $time);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->get_datetime_column_value('created_at');
    }

    public function orderByCreatedAt($order = null)
    {
        $this->order_by('created_at', ($order && mb_strtolower($order) === 'asc') ? 'asc' : 'desc');

        return $this;
    }

    /**
     * @param \BetaKiller\Model\RevisionModelInterface $actual
     *
     * @return $this
     */
    public function filterPending(RevisionModelInterface $actual)
    {
        $this->filter_datetime_column_value('created_at', $actual->getCreatedAt(), '>');
        return $this;
    }

    /**
     * Returns new revision model if new revision was created or null if not
     *
     * @return \BetaKiller\Model\RevisionModelInterface|null
     */
    public function createNewRevisionIfChanged()
    {
        if (!$this->changed()) {
            return null;
        }

        // Save current values
        $values = $this->object();

        /** @var \BetaKiller\Model\AbstractRevisionOrmModel $model */
        $model = $this->model_factory();

        // Fill ActiveRecord with saved values
        $model->values($values);

        // Create DB record and return it
        $model->create();

        return $model;
    }
}
