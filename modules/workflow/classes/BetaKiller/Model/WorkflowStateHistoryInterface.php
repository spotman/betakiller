<?php

declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Workflow\WorkflowStateInterface;

interface WorkflowStateHistoryInterface extends CreatedByAtInterface
{
    public static function createFrom(
        UserInterface $byUser,
        HasWorkflowStateWithHistoryInterface $entity,
        WorkflowStateInterface $state,
        string $transitionName
    ): WorkflowStateHistoryInterface;

    public function getState(): WorkflowStateInterface;
    public function getTransitionName(): string;
}
