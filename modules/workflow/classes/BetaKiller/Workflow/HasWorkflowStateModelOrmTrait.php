<?php
namespace BetaKiller\Workflow;

trait HasWorkflowStateModelOrmTrait
{
    protected function configureWorkflowModelRelation(): void
    {
        $statusRelationKey = $this->getWorkflowStatusRelationKey();

        $this->belongs_to([
            $statusRelationKey => [
                'model'       => $this->getWorkflowStateModelName(),
                'foreign_key' => $this->getWorkflowStatusForeignKey(),
            ],
        ]);

        $this->load_with([$statusRelationKey]);
    }

    /**
     * @return WorkflowStateInterface
     */
    public function getWorkflowState(): WorkflowStateInterface
    {
        return $this->getRelatedEntity($this->getWorkflowStatusRelationKey());
    }

    /**
     * @param \BetaKiller\Workflow\WorkflowStateInterface $target
     *
     * @return void
     * @throws \BetaKiller\Workflow\StatusWorkflowException
     */
    public function changeWorkflowState(WorkflowStateInterface $target): void
    {
        // Check if model has current status
        if (!$this->hasWorkflowState()) {
            throw new StatusWorkflowException('Model must have current status before changing it');
        }

        $this->setWorkflowStatus($target);
    }

    /**
     * @param \BetaKiller\Workflow\WorkflowStateInterface $status
     *
     * @return void
     * @throws \BetaKiller\Workflow\StatusWorkflowException
     */
    public function initWorkflowState(WorkflowStateInterface $status): void
    {
        // Ensure that model has no current status
        if ($this->hasWorkflowState()) {
            throw new StatusWorkflowException('Model ":name" can not have workflow status before initializing', [
                ':name' => $this::getModelName(),
            ]);
        }

        $this->setWorkflowStatus($status);
    }

    /**
     * @return bool
     */
    public function hasWorkflowState(): bool
    {
        return (bool)$this->get($this->getWorkflowStatusForeignKey());
    }

    /**
     * @param WorkflowStateInterface $target
     *
     * @return void
     */
    protected function setWorkflowStatus(WorkflowStateInterface $target): void
    {
        $this->set($this->getWorkflowStatusRelationKey(), $target);
    }

    protected function getWorkflowStatusRelationKey(): string
    {
        return 'status';
    }

    /**
     * @return string
     */
    abstract protected function getWorkflowStatusForeignKey(): string;
}
