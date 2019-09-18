<?php
namespace BetaKiller\Workflow;

use BetaKiller\Acl\Resource\HasWorkflowStateAclResourceInterface;
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

    public function __construct(WorkflowConfigInterface $config, AclHelper $acl, RepositoryFactory $repoFactory)
    {
        $this->config      = $config;
        $this->acl         = $acl;
        $this->repoFactory = $repoFactory;
    }

    /**
     * @param \BetaKiller\Workflow\HasWorkflowStateModelInterface $model
     * @param string                                              $transition
     * @param \BetaKiller\Model\UserInterface                     $user
     *
     * @throws \BetaKiller\Workflow\StatusException
     */
    public function doTransition(HasWorkflowStateModelInterface $model, string $transition, UserInterface $user): void
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
        if ($model->isWorkflowStateHistoryEnabled()) {
            // TODO Model_Status_Workflow_History + tables in selected projects
            // TODO Store user, transition, related model_id (auto timestamp in mysql column)
            throw new NotImplementedHttpException();
        }
    }

    public function isTransitionAllowed(
        HasWorkflowStateModelInterface $model,
        string $codename,
        UserInterface $user
    ): bool {
        $resource = $this->acl->getEntityAclResource($model);

        if (!$resource instanceof HasWorkflowStateAclResourceInterface) {
            throw new WorkflowStateException('Acl resource ":id" must implement :class', [
                ':id'    => $resource->getResourceId(),
                ':class' => HasWorkflowStateAclResourceInterface::class,
            ]);
        }

        return $this->acl->isPermissionAllowed($user, $resource, $codename);
    }

    public function setStartState(HasWorkflowStateModelInterface $model): void
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

    private function createStateRepositoryFor(HasWorkflowStateModelInterface $model): WorkflowStateRepositoryInterface
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
