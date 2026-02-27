<?php
namespace BetaKiller\Workflow;

use BetaKiller\Model\HasWorkflowStateInterface;
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
     * @param string                                      $codename
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
}
