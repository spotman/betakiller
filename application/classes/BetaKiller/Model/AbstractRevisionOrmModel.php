<?php
namespace BetaKiller\Model;

class AbstractRevisionOrmModel extends \ORM implements RevisionModelInterface
{
    /**
     * @return \BetaKiller\Model\RevisionModelInterface
     */
    public function getLatestRevision()
    {
        /** @var \BetaKiller\Model\AbstractRevisionOrmModel $orm */
        $orm = $this->model_factory();

        return $orm->orderByCreatedAt()->find();
    }

    public function setCreatedBy(UserInterface $model)
    {
        return $this->set('created_by', $model);
    }

    protected function orderByCreatedAt($order = null)
    {
        $this->order_by('created_at', ($order && mb_strtolower($order) === 'asc') ? 'asc' : 'desc');

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
