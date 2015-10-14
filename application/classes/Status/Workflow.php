<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Status_Workflow {

    /**
     * @var Status_Related_Model
     */
    protected $model;

    public function __construct(Status_Related_Model $model)
    {
        $this->model = $model;
    }

    public function do_transition($codename)
    {
        // Find allowed target transition by provided codename
        $target_transition = $this->find_target_transition($codename);

        $this->model->do_status_transition($target_transition);

        if ( $this->is_history_enabled() )
        {
            // TODO Model_Status_Workflow_History + tables in selected projects
            // TODO Store user, transition, related model_id (auto timestamp in mysql column)
        }
    }

    /**
     * Override this in child class if you need status transition history
     * @return bool
     */
    protected function is_history_enabled()
    {
        return FALSE;
    }

    protected function find_target_transition($codename)
    {
        $targets = $this->model->get_target_transitions();

        foreach ( $targets as $target )
        {
            if ( $target->get_codename() == $codename )
                return $target;
        }

        throw new Status_Exception('Can not find target transition by codename :transition from status :status', [
            ':transition'   =>  $codename,
            ':status'       =>  $this->model->get_current_status()->get_codename(),
        ]);
    }

}
