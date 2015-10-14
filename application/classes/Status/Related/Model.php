<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Status_Related_Model extends ORM {

    /**
     * @var Status_Workflow
     */
    protected $_workflow_instance;

    protected function _initialize()
    {
        $status_relation_key = $this->get_status_relation_key();

        $this->belongs_to([
            $status_relation_key    =>  [
                'model'             =>  $this->get_status_relation_model_name(),
                'foreign_key'       =>  $this->get_status_relation_foreign_key(),
            ]
        ]);

        $this->load_with([$status_relation_key]);

        parent::_initialize();
    }

    /**
     * @return Status_Model
     */
    public function get_current_status()
    {
        return $this->get_status_relation();
    }

    public function change_status(Status_Model $target)
    {
        $current = $this->get_current_status();

        if ( ! $current->has_target($target) )
            throw new Status_Exception('Target status is not allowed');

        return $this->set_current_status($target);
    }

    public function do_status_transition(Status_Transition_Model $transition)
    {
        if ( $transition->get_source_node()->pk() != $this->get_current_status()->pk() )
            throw new Status_Exception('Only transitions from current status are allowed');

        /** @var Status_Model $target_status */
        $target_status = $transition->get_target_node();

        return $this->set_current_status($target_status);
    }

    public function is_status_transition_allowed($codename)
    {
        return $this->get_current_status()->is_target_transition_allowed($codename);
    }

    /**
     * @return Status_Model[]|NULL
     */
    public function get_allowed_statuses()
    {
        return $this->get_current_status()->get_target_nodes();
    }

    /**
     * @return Database_Result|Status_Transition_Model[]
     */
    public function get_source_transitions()
    {
        return $this->get_current_status()->get_source_transitions();
    }

    /**
     * @return Database_Result|Status_Transition_Model[]
     */
    public function get_target_transitions()
    {
        return $this->get_current_status()->get_target_transitions();
    }

    /**
     * @return Database_Result|Status_Transition_Model[]
     */
    public function get_allowed_source_transitions()
    {
        return $this->get_current_status()->get_allowed_source_transitions();
    }

    /**
     * @return Database_Result|Status_Transition_Model[]
     */
    public function get_allowed_target_transitions()
    {
        return $this->get_current_status()->get_allowed_target_transitions();
    }

    /**
     * @return Status_Model|NULL
     */
    public function set_start_status()
    {
        /** @var Status_Model $start */
        $start = $this->status_model_factory()->get_start_node();

        return $this->set_current_status($start);
    }

    /**
     * @param integer $status_id
     * @param bool $not_equal
     * @return $this
     */
    public function filter_status($status_id, $not_equal = FALSE)
    {
        return $this->where($this->get_status_relation_foreign_key(), $not_equal ? '<>' : '=', $status_id);
    }

    /**
     * @param array $status_ids
     * @param bool $not_equal
     * @return $this
     */
    public function filter_statuses(array $status_ids, $not_equal = FALSE)
    {
        return $this->where($this->get_status_relation_foreign_key(), $not_equal ? 'NOT IN' : 'IN', $status_ids);
    }

    public function get_status_id()
    {
        return $this->get($this->get_status_relation_foreign_key());
    }

    /**
     * @param int|array|NULL $id
     * @return Status_Model
     */
    public function status_model_factory($id = NULL)
    {
        return ORM::factory($this->get_status_relation_model_name(), $id);
    }

    protected function workflow()
    {
        if ( ! $this->_workflow_instance )
            $this->_workflow_instance = $this->workflow_factory();

        return $this->_workflow_instance;
    }

    /**
     * @return Status_Workflow
     */
    protected function workflow_factory()
    {
        return Status_Workflow_Factory::instance()->create($this->get_workflow_name(), $this);
    }

    /**
     * Returns key for workflow factory
     *
     * @return string
     */
    abstract protected function get_workflow_name();

    /**
     * @return Status_Model
     */
    protected function get_status_relation()
    {
        return $this->get($this->get_status_relation_key());
    }

    /**
     * @param Status_Model $target
     * @return $this
     */
    protected function set_current_status(Status_Model $target)
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
