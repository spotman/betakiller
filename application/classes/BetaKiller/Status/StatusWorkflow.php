<?php
namespace BetaKiller\Status;

use BetaKiller\Model\UserInterface;

abstract class StatusWorkflow implements StatusWorkflowInterface
{
    /**
     * @var StatusRelatedModelInterface
     */
    protected $model;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    protected $user;

    public function __construct(StatusRelatedModelInterface $model, UserInterface $user)
    {
        $this->model = $model;
        $this->user = $user;
    }

    /**
     * @param string $codename
     *
     * @throws \BetaKiller\Status\StatusException
     */
    public function doTransition(string $codename): void
    {
        // Find allowed target transition by provided codename
        $targetTransition = $this->findTargetTransition($codename);

        // Make custom check
        $this->customTargetTransitionCheck($targetTransition);

        // Process transition
        $this->model->doStatusTransition($targetTransition);

        // Write history record if needed
        if ($this->isHistoryEnabled()) {
            // TODO Model_Status_Workflow_History + tables in selected projects
            // TODO Store user, transition, related model_id (auto timestamp in mysql column)
            throw new \HTTP_Exception_501('Not implemented yet');
        }
    }

    protected function customTargetTransitionCheck(StatusTransitionModelInterface $transition): void
    {
        // Empty by default
    }

    public function isTransitionAllowed(string $codename): bool
    {
        return $this->model->isStatusTransitionAllowed($codename, $this->user);
    }

    /**
     * Override this in child class if you need status transition history
     * @return bool
     */
    protected function isHistoryEnabled(): bool
    {
        return FALSE;
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Status\StatusTransitionModelInterface
     * @throws \BetaKiller\Status\StatusException
     */
    protected function findTargetTransition(string $codename): StatusTransitionModelInterface
    {
        $targets = $this->model->getTargetTransitions();

        foreach ($targets as $target) {
            if ($target->getCodename() === $codename) {
                return $target;
            }
        }

        throw new StatusException('Can not find target transition by codename :transition from status :status', [
            ':transition' => $codename,
            ':status'     => $this->model->getCurrentStatus()->getCodename(),
        ]);
    }
}
