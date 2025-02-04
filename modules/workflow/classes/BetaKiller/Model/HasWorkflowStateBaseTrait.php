<?php
namespace BetaKiller\Model;

use BetaKiller\Workflow\WorkflowStateException;
use BetaKiller\Workflow\WorkflowStateInterface;

trait HasWorkflowStateBaseTrait
{
    /**
     * @param \BetaKiller\Workflow\WorkflowStateInterface $target
     *
     * @return void
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    public function changeWorkflowState(WorkflowStateInterface $target): void
    {
        // Check if model has current status
        if (!$this->hasWorkflowState()) {
            throw new WorkflowStateException('Model must have current status before changing it');
        }

        $this->setWorkflowState($target);
    }

    /**
     * @param \BetaKiller\Workflow\WorkflowStateInterface $status
     *
     * @return void
     * @throws \BetaKiller\Workflow\WorkflowStateException|\Kohana_Exception
     */
    public function initWorkflowState(WorkflowStateInterface $status): void
    {
        // Ensure that model has no current status
        if ($this->hasWorkflowState()) {
            throw new WorkflowStateException('Model ":name" can not have workflow status before initializing', [
                ':name' => $this::getModelName(),
            ]);
        }

        $this->setWorkflowState($status);
    }

    /**
     * @param \BetaKiller\Workflow\WorkflowStateInterface|string $state
     *
     * @return bool
     * @throws \Kohana_Exception
     */
    protected function isInWorkflowState(WorkflowStateInterface|string $state): bool
    {
        $codename = $state instanceof WorkflowStateInterface ? $state->getCodename() : $state;

        return $this->getWorkflowState()->getCodename() === $codename;
    }

    /**
     * @param string[] $states
     *
     * @return bool
     */
    protected function isInWorkflowStates(array $states): bool
    {
        return \in_array($this->getWorkflowState()->getCodename(), $states, true);
    }

    abstract protected function setWorkflowState(WorkflowStateInterface $target): void;
}
