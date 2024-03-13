<?php
namespace BetaKiller\Workflow;

use BetaKiller\Acl\EntityPermissionResolverInterface;
use BetaKiller\Config\WorkflowConfigInterface;
use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Factory\RepositoryFactoryInterface;
use BetaKiller\Model\HasWorkflowStateInterface;
use BetaKiller\Model\HasWorkflowStateWithHistoryInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\WorkflowStateRepositoryInterface;

final class StatusWorkflow implements StatusWorkflowInterface
{
    /**
     * @var \BetaKiller\Config\WorkflowConfigInterface
     */
    private WorkflowConfigInterface $config;

    /**
     * @var \BetaKiller\Factory\RepositoryFactoryInterface
     */
    private RepositoryFactoryInterface $repoFactory;

    /**
     * @var \BetaKiller\Acl\EntityPermissionResolverInterface
     */
    private EntityPermissionResolverInterface $permissionResolver;

    /**
     * StatusWorkflow constructor.
     *
     * @param \BetaKiller\Config\WorkflowConfigInterface        $config
     * @param \BetaKiller\Acl\EntityPermissionResolverInterface $permissionResolver
     * @param \BetaKiller\Factory\RepositoryFactoryInterface    $repoFactory
     */
    public function __construct(
        WorkflowConfigInterface           $config,
        EntityPermissionResolverInterface $permissionResolver,
        RepositoryFactoryInterface        $repoFactory
    ) {
        $this->config             = $config;
        $this->repoFactory        = $repoFactory;
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * @param \BetaKiller\Model\HasWorkflowStateInterface $model
     * @param string                                      $transition
     * @param \BetaKiller\Model\UserInterface             $user
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
            throw new WorkflowStateException(
                'Transition ":name" in ":model" ID ":id" is not allowed for user ":user"',
                [
                    ':model' => $model::getModelName(),
                    ':id'    => $model->getID(),
                    ':name'  => $transition,
                    ':user'  => $user->getID(),
                ]
            );
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
        string                    $codename,
        UserInterface             $user
    ): bool {
        return $this->permissionResolver->isAllowed($user, $model, $codename);
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
