<?php
namespace BetaKiller\Workflow;

use BetaKiller\Model\AbstractEntityInterface;

interface HasWorkflowStateInterface extends AbstractEntityInterface
{
    /**
     * @param \BetaKiller\Workflow\WorkflowStateInterface $target
     *
     * @return void
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    public function initWorkflowState(WorkflowStateInterface $target): void;

    /**
     * @param \BetaKiller\Workflow\WorkflowStateInterface $target
     *
     * @return void
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    public function changeWorkflowState(WorkflowStateInterface $target): void;

    /**
     * @return bool
     */
    public function hasWorkflowState(): bool;

    /**
     * @return WorkflowStateInterface
     */
    public function getWorkflowState(): WorkflowStateInterface;

    /**
     * @return string
     */
    public static function getWorkflowStateModelName(): string;
}
