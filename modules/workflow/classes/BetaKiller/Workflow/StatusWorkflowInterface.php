<?php
namespace BetaKiller\Workflow;

use BetaKiller\Model\HasWorkflowStateInterface;
use BetaKiller\Model\HasWorkflowStateWithHistoryInterface;
use BetaKiller\Model\UserInterface;

interface StatusWorkflowInterface
{
    /**
     * @param \BetaKiller\Model\HasWorkflowStateInterface $model
     * @param \BetaKiller\Model\UserInterface             $user
     *
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    public function setStartState(HasWorkflowStateInterface $model, UserInterface $user): void;

    /**
     * @param \BetaKiller\Model\HasWorkflowStateInterface $model
     * @param string                                      $transition
     * @param \BetaKiller\Model\UserInterface             $user
     *
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    public function doTransition(HasWorkflowStateInterface $model, string $transition, UserInterface $user): void;

    /**
     * @param \BetaKiller\Model\HasWorkflowStateInterface $model
     * @param string                                      $codename
     * @param \BetaKiller\Model\UserInterface             $user
     *
     * @return bool
     */
    public function isTransitionAllowed(HasWorkflowStateInterface $model, string $codename, UserInterface $user): bool;

    /**
     * Adds initial record to state history
     * Must be called manually, right after first saving of the related entity (depends on the Entity ID)
     *
     * @param \BetaKiller\Model\UserInterface                        $user
     * @param \BetaKiller\Model\HasWorkflowStateWithHistoryInterface $model
     *
     * @return void
     */
    public function addInitialHistoryRecord(UserInterface $user, HasWorkflowStateWithHistoryInterface $model): void;
}
