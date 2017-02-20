<?php
namespace BetaKiller\Status;

abstract class StatusWorkflow implements StatusWorkflowInterface
{
    /**
     * @var StatusRelatedModelInterface
     */
    protected $model;

    public function __construct(StatusRelatedModelInterface $model)
    {
        $this->model = $model;
    }

    public function do_transition($codename)
    {
        // Find allowed target transition by provided codename
        $target_transition = $this->find_target_transition($codename);

        // Make custom check
        $this->custom_target_transition_check($target_transition);

        // Process transition
        $this->model->do_status_transition($target_transition);

        if ($this->is_history_enabled()) {
            // TODO Model_Status_Workflow_History + tables in selected projects
            // TODO Store user, transition, related model_id (auto timestamp in mysql column)
        }
    }

    protected function custom_target_transition_check(StatusTransitionModelInterface $transition)
    {
        // Empty by default
    }

    public function is_transition_allowed($codename)
    {
        return $this->model->is_status_transition_allowed($codename);
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

        foreach ($targets as $target) {
            if ($target->get_codename() == $codename)
                return $target;
        }

        throw new StatusException('Can not find target transition by codename :transition from status :status', [
            ':transition' => $codename,
            ':status'     => $this->model->get_current_status()->get_codename(),
        ]);
    }

}
