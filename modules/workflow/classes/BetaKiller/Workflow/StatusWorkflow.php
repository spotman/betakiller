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
     * @var array<string, WorkflowStateRepositoryInterface>
     */
    private array $stateRepoCache = [];

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
        $targetState      = $this->getStateRepositoryFor($model)->getByCodename($targetStateName);

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
        $this->addHistoryRecord($user, $model, $targetState, $transition);
    }

    public function isTransitionAllowed(
        HasWorkflowStateInterface $model,
        string                    $codename,
        UserInterface             $user
    ): bool {
        return $this->permissionResolver->isAllowed($user, $model, $codename);
    }

    public function setStartState(HasWorkflowStateInterface $model, UserInterface $user): void
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

        $startState = $this->getStateRepositoryFor($model)->getStartState();

        $model->initWorkflowState($startState);

        $this->addHistoryRecord($user, $model, $startState, 'init');
    }

    private function getStateRepositoryFor(HasWorkflowStateInterface $model): WorkflowStateRepositoryInterface
    {
        $stateModelName = $model::getWorkflowStateModelName();

        return $this->stateRepoCache[$stateModelName] ??= $this->createStateRepositoryFor($stateModelName);
    }

    private function createStateRepositoryFor(string $stateModelName): WorkflowStateRepositoryInterface
    {
        $stateRepo = $this->repoFactory->create($stateModelName);

        if (!$stateRepo instanceof WorkflowStateRepositoryInterface) {
            throw new WorkflowStateException('Repo ":name" must implement :class', [
                ':name'  => $stateModelName,
                ':class' => WorkflowStateRepositoryInterface::class,
            ]);
        }

        return $stateRepo;
    }

    private function addHistoryRecord(UserInterface $user, HasWorkflowStateInterface $model, WorkflowStateInterface $state, string $transition): void
    {
        if ($model instanceof HasWorkflowStateWithHistoryInterface) {
            $model->addWorkflowStateHistory($user, $state, $transition);
        }
    }
}
