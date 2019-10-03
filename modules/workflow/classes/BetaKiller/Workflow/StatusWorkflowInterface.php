<?php
namespace BetaKiller\Workflow;

use BetaKiller\Model\UserInterface;

interface StatusWorkflowInterface
{
    public const CLASS_NS     = 'Workflow';
    public const CLASS_SUFFIX = 'Workflow';

    /**
     * @param \BetaKiller\Workflow\HasWorkflowStateInterface $model
     *
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    public function setStartState(HasWorkflowStateInterface $model): void;

    /**
     * @param \BetaKiller\Workflow\HasWorkflowStateInterface $model
     * @param string                                         $codename
     * @param \BetaKiller\Model\UserInterface                $user
     *
     * @throws \BetaKiller\Workflow\WorkflowStateException
     */
    public function doTransition(HasWorkflowStateInterface $model, string $codename, UserInterface $user): void;

    /**
     * @param \BetaKiller\Workflow\HasWorkflowStateInterface $model
     * @param string                                         $codename
     * @param \BetaKiller\Model\UserInterface                $user
     *
     * @return bool
     */
    public function isTransitionAllowed(HasWorkflowStateInterface $model, string $codename, UserInterface $user): bool;
}
