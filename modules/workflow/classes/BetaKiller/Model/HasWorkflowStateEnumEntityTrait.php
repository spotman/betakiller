<?php
namespace BetaKiller\Model;

use BetaKiller\Workflow\WorkflowStateInterface;

trait HasWorkflowStateEnumEntityTrait
{
    use HasWorkflowStateBaseTrait;

    /**
     * @return WorkflowStateInterface
     * @throws \Kohana_Exception
     */
    public function getWorkflowState(): WorkflowStateInterface
    {
        $raw = (string)$this->get(static::getWorkflowStateForeignKey());

        return $this->createWorkflowStateFromColumnValue($raw);
    }

    /**
     * @return bool
     * @throws \Kohana_Exception
     */
    public function hasWorkflowState(): bool
    {
        return !empty($this->get(static::getWorkflowStateForeignKey()));
    }

    /**
     * @param WorkflowStateInterface $target
     *
     * @return void
     * @throws \Kohana_Exception
     */
    protected function setWorkflowState(WorkflowStateInterface $target): void
    {
        $value = $this->exportWorkflowStateToColumnValue($target);

        $this->set(static::getWorkflowStateForeignKey(), $value);
    }

    /**
     * @return string
     */
    abstract public static function getWorkflowStateForeignKey(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Workflow\WorkflowStateInterface
     */
    abstract protected function createWorkflowStateFromColumnValue(string $value): WorkflowStateInterface;

    /**
     * @param \BetaKiller\Workflow\WorkflowStateInterface $state
     *
     * @return \BetaKiller\Workflow\WorkflowStateInterface
     */
    abstract protected function exportWorkflowStateToColumnValue(WorkflowStateInterface $state): string;
}
