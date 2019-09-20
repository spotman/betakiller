<?php
namespace BetaKiller\Workflow;

trait HasWorkflowStateModelOrmTrait
{
    protected function configureWorkflowStateRelation(): void
    {
        $statusRelationKey = static::getWorkflowStateRelationKey();

        $this->belongs_to([
            $statusRelationKey => [
                'model'       => static::getWorkflowStateModelName(),
                'foreign_key' => static::getWorkflowStateForeignKey(),
            ],
        ]);

        $this->load_with([$statusRelationKey]);
    }

    /**
     * @return WorkflowStateInterface
     */
    public function getWorkflowState(): WorkflowStateInterface
    {
        return $this->getRelatedEntity(static::getWorkflowStateRelationKey());
    }

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
     * @throws \BetaKiller\Workflow\WorkflowStateException
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
     * @return bool
     */
    public function hasWorkflowState(): bool
    {
        return (bool)$this->get(static::getWorkflowStateForeignKey());
    }

    /**
     * @param WorkflowStateInterface $target
     *
     * @return void
     */
    protected function setWorkflowState(WorkflowStateInterface $target): void
    {
        $this->set(static::getWorkflowStateRelationKey(), $target);
    }

    public static function getWorkflowStateRelationKey(): string
    {
        return static::getModelName().'-status';
    }

    /**
     * @return string
     */
    abstract public static function getWorkflowStateForeignKey(): string;
}
