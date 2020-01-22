<?php
namespace BetaKiller\Workflow;

use BetaKiller\Config\WorkflowConfigInterface;
use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Factory\RepositoryFactory;
use BetaKiller\Helper\AclHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\WorkflowStateRepositoryInterface;

final class StatusWorkflow implements StatusWorkflowInterface
{
    /**
     * @var \BetaKiller\Config\WorkflowConfigInterface
     */
    private $config;

    /**
     * @var \BetaKiller\Helper\AclHelper
     */
    private $acl;

    /**
     * @var \BetaKiller\Factory\RepositoryFactory
     */
    private $repoFactory;

    /**
     * StatusWorkflow constructor.
     *
     * @param \BetaKiller\Config\WorkflowConfigInterface $config
     * @param \BetaKiller\Helper\AclHelper               $acl
     * @param \BetaKiller\Factory\RepositoryFactory      $repoFactory
     */
    public function __construct(WorkflowConfigInterface $config, AclHelper $acl, RepositoryFactory $repoFactory)
    {
        $this->config      = $config;
        $this->acl         = $acl;
        $this->repoFactory = $repoFactory;
    }

    /**
     * @param \BetaKiller\Workflow\HasWorkflowStateInterface $model
     * @param string                                         $transition
     * @param \BetaKiller\Model\UserInterface                $user
     *
     * @throws \BetaKiller\Workflow\WorkflowException
     */
    public function doTransition(HasWorkflowStateInterface $model, string $transition, UserInterface $user): void
    {
        $modelName = $model::getModelName();

        // Detect target state (if defined)
        $currentStateName = $model->getWorkflowState()->getCodename();
        $targetStateName  = $this->config->getStateTransitionTarget($modelName, $currentStateName, $transition);
        $targetState      = $this->createStateRepositoryFor($model)->getByCodename($targetStateName);

        if (!$this->isTransitionAllowed($model, $transition, $user)) {
            throw new WorkflowStateException('Transition ":name" in ":model" is not allowed for user ":user"', [
                ':model' => $model::getModelName(),
                ':name'  => $transition,
                ':user'  => $user->getID(),
            ]);
        }

        // Update state
        $model->changeWorkflowState($targetState);

        // Write history record if needed
        if ($model instanceof HasWorkflowStateWithHistoryInterface) {
            // TODO Model_Status_Workflow_History + tables in selected projects
            // TODO Store user, transition, related model_id (auto timestamp in mysql column)
            throw new NotImplementedHttpException();
        }
    }

    public function isTransitionAllowed(
        HasWorkflowStateInterface $model,
        string $codename,
        UserInterface $user
    ): bool {
        return $this->acl->isEntityPermissionAllowed($user, $model, $codename);
    }

    public function setStartState(HasWorkflowStateInterface $model): void
    {
        if ($model->hasWorkflowState()) {
            throw new WorkflowStateException(
                'Can not set start status for :name [:id] coz it is in [:status] status already',
                [
                    ':name'   => $model::getModelName(),
                    ':id'     => $model->getID(),
                    ':status' => $model->getWorkflowState()->getCodename(),
                ]
            );
        }

        $startState = $this->createStateRepositoryFor($model)->getStartState();

        $model->initWorkflowState($startState);
    }

    private function createStateRepositoryFor(HasWorkflowStateInterface $model): WorkflowStateRepositoryInterface
    {
        $stateModelName = $model::getWorkflowStateModelName();
        $stateRepo      = $this->repoFactory->create($stateModelName);

        if (!$stateRepo instanceof WorkflowStateRepositoryInterface) {
            throw new WorkflowStateException('Repo ":name" must implement :class', [
                ':name'  => $stateModelName,
                ':class' => WorkflowStateRepositoryInterface::class,
            ]);
        }

        return $stateRepo;
    }
}
