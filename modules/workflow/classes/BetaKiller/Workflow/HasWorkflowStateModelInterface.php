<?php
namespace BetaKiller\Workflow;

use BetaKiller\Model\AbstractEntityInterface;

interface HasWorkflowStateModelInterface extends AbstractEntityInterface
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

    /**
     * Return TRUE if you need status transition history
     *
     * @return bool
     */
    public function isWorkflowStateHistoryEnabled(): bool;
}
