<?php
namespace BetaKiller\Status;

use BetaKiller\Graph\GraphNodeModelInterface;
use BetaKiller\Graph\GraphTransitionModelInterface;
use ORM;

trait StatusRelatedModelOrmTrait
{
    /**
     * @var StatusWorkflowInterface
     */
    protected $_workflow_instance;

    protected function initialize_related_model_relation()
    {
        $status_relation_key = $this->get_status_relation_key();

        $this->belongs_to([
            $status_relation_key => [
                'model'       => $this->get_status_relation_model_name(),
                'foreign_key' => $this->get_status_relation_foreign_key(),
            ],
        ]);

        $this->load_with([$status_relation_key]);
    }

    /**
     * @return StatusModelInterface
     */
    public function get_current_status()
    {
        return $this->get_status_relation();
    }

    /**
     * @param \BetaKiller\Status\StatusModelInterface $target
     *
     * @return $this
     * @throws \BetaKiller\Status\StatusException
     */
    public function change_status(StatusModelInterface $target)
    {
        $current = $this->get_current_status();

        // Check if model has current status
        if (!$current->get_id()) {
            throw new StatusException('Model must have current status before changing it');
        }

        if (!$current->has_target($target)) {
            throw new StatusException('Target status :target is not allowed', [':target' => $target->get_codename()]);
        }

        return $this->set_current_status($target);
    }

    /**
     * @param \BetaKiller\Status\StatusModelInterface $status
     *
     * @return $this
     * @throws \BetaKiller\Status\StatusException
     */
    public function init_status(StatusModelInterface $status)
    {
        $current = $this->get_current_status();

        // Check if model has no current status
        if ($current->get_id()) {
            throw new StatusException('Model can not have current status before initializing');
        }

        return $this->set_current_status($status);
    }

    /**
     * @param int $id
     *
     * @return \BetaKiller\Status\StatusModelInterface
     */
    public function get_status_by_id($id)
    {
        return $this->get_status_relation()->model_factory($id);
    }

    public function do_status_transition(StatusTransitionModelInterface $transition)
    {
        if ($transition->get_source_node()->get_id() !== $this->get_current_status()->get_id()) {
            throw new StatusException('Only transitions from current status are allowed');
        }

        /** @var StatusModelInterface $target_status */
        $target_status = $transition->get_target_node();

        return $this->set_current_status($target_status);
    }

    public function is_status_transition_allowed($codename)
    {
        return $this->get_current_status()->is_target_transition_allowed($codename);
    }

    /**
     * @return StatusModelInterface[]|GraphNodeModelInterface[]
     */
    public function get_allowed_statuses()
    {
        return $this->get_current_status()->get_target_nodes();
    }

    /**
     * @return StatusTransitionModelInterface[]|GraphTransitionModelInterface[]
     */
    public function get_source_transitions()
    {
        return $this->get_current_status()->get_source_transitions();
    }

    /**
     * @return StatusTransitionModelInterface[]|GraphTransitionModelInterface[]
     */
    public function get_target_transitions()
    {
        return $this->get_current_status()->get_target_transitions();
    }

    /**
     * @return StatusTransitionModelInterface[]|GraphTransitionModelInterface[]
     */
    public function get_allowed_source_transitions()
    {
        return $this->get_current_status()->get_allowed_source_transitions();
    }

    /**
     * @return StatusTransitionModelInterface[]|GraphTransitionModelInterface[]
     */
    public function get_allowed_target_transitions()
    {
        return $this->get_current_status()->get_allowed_target_transitions();
    }

    /**
     * @return string[]
     */
    public function get_allowed_target_transitions_codenames()
    {
        $output = [];

        foreach ($this->get_allowed_target_transitions() as $transition) {
            $output[] = $transition->get_codename();
        }

        return $output;
    }

    /**
     * @return static
     */
    public function set_start_status()
    {
        /** @var StatusModelInterface $start */
        $start = $this->status_model_factory()->get_start_node();

        return $this->set_current_status($start);
    }

    /**
     * @param integer $status_id
     * @param bool    $not_equal
     *
     * @return $this
     */
    public function filter_status_id($status_id, $not_equal = FALSE)
    {
        $col = $this->object_column($this->get_status_relation_foreign_key());

        return $this->where($col, $not_equal ? '<>' : '=', $status_id);
    }

    /**
     * @param StatusModelInterface  $status
     * @param bool                  $not_equal
     *
     * @return $this
     */
    public function filter_status(StatusModelInterface $status, $not_equal = FALSE)
    {
        return $this->filter_status_id($status->get_id(), $not_equal);
    }

    /**
     * @param array $status_ids
     * @param bool  $not_equal
     *
     * @return $this
     */
    public function filter_statuses(array $status_ids, $not_equal = FALSE)
    {
        $col = $this->object_column($this->get_status_relation_foreign_key());

        return $this->where($col, $not_equal ? 'NOT IN' : 'IN', $status_ids);
    }

    public function get_status_id()
    {
        return $this->get($this->get_status_relation_foreign_key());
    }

    public function has_current_status()
    {
        return (bool) $this->get_status_id();
    }

    /**
     * @param int|array|NULL $id
     *
     * @return StatusModelOrm|\BetaKiller\Utils\Kohana\ORM\OrmInterface
     */
    public function status_model_factory($id = NULL)
    {
        return ORM::factory($this->get_status_relation_model_name(), $id);
    }

    protected function workflow()
    {
        if (!$this->_workflow_instance) {
            $this->_workflow_instance = $this->workflow_factory();
        }

        return $this->_workflow_instance;
    }

    /**
     * @return StatusWorkflowInterface
     */
    protected function workflow_factory()
    {
        return StatusWorkflowFactory::instance()->create($this->get_workflow_name(), $this);
    }

    /**
     * Returns key for workflow factory
     *
     * @return string
     */
    abstract protected function get_workflow_name();

    /**
     * @return StatusModelOrm
     */
    protected function get_status_relation()
    {
        return $this->get($this->get_status_relation_key());
    }

    /**
     * @param StatusModelInterface $target
     *
     * @return $this
     */
    protected function set_current_status(StatusModelInterface $target)
    {
        return $this->set($this->get_status_relation_key(), $target);
    }

    protected function get_status_relation_key()
    {
        return 'status';
    }

    /**
     * @return string
     */
    abstract protected function get_status_relation_model_name();

    /**
     * @return string
     */
    abstract protected function get_status_relation_foreign_key();
}
