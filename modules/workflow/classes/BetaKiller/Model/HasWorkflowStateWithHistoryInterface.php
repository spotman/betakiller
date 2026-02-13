<?php

namespace BetaKiller\Model;

use BetaKiller\Workflow\WorkflowStateInterface;

interface HasWorkflowStateWithHistoryInterface extends HasWorkflowStateInterface
{
    public function addWorkflowStateHistory(
        UserInterface $byUser,
        WorkflowStateInterface $state,
        string $transitionName
    ): WorkflowStateHistoryInterface;

    /**
     * @return WorkflowStateHistoryInterface[]
     */
    public function getWorkflowStateHistory(): array;
}
