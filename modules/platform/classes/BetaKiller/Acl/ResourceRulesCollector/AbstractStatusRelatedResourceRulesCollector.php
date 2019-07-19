<?php
namespace BetaKiller\Acl\ResourceRulesCollector;

use BetaKiller\Acl\Resource\HasWorkflowStateAclResourceInterface;
use BetaKiller\Config\WorkflowConfigInterface;
use BetaKiller\Factory\RepositoryFactory;
use BetaKiller\Repository\RepositoryException;
use BetaKiller\Repository\WorkflowStateRepositoryInterface;
use BetaKiller\Workflow\StatusWorkflowException;
use Spotman\Acl\ResourceInterface;
use Spotman\Acl\ResourceRulesCollector\AbstractResourceRulesCollector;

abstract class AbstractStatusRelatedResourceRulesCollector extends AbstractResourceRulesCollector
{
    /**
     * @var \BetaKiller\Config\WorkflowConfigInterface
     */
    private $config;

    /**
     * @var \BetaKiller\Factory\RepositoryFactory
     */
    private $repoFactory;

    /**
     * ContentPostResourceRulesCollector constructor.
     *
     * @param \BetaKiller\Config\WorkflowConfigInterface $config
     * @param \BetaKiller\Factory\RepositoryFactory      $repoFactory
     */
    public function __construct(WorkflowConfigInterface $config, RepositoryFactory $repoFactory)
    {
        $this->config      = $config;
        $this->repoFactory = $repoFactory;
    }

    /**
     * Key=>Value pairs where key is a permission identity and value is an array of roles
     *
     * @param \Spotman\Acl\ResourceInterface $resource
     *
     * @return string[][]
     */
    protected function getPermissionsRoles(ResourceInterface $resource): array
    {
        if (!$resource instanceof HasWorkflowStateAclResourceInterface) {
            throw new RepositoryException('Resource ":name" must implement :class', [
                ':name'  => get_class($resource),
                ':class' => HasWorkflowStateAclResourceInterface::class,
            ]);
        }

        $modelName = $resource->getResourceId();

        $statusModelName = $this->config->getStateModelName($modelName);

        $statusRepo = $this->repoFactory->create($statusModelName);

        if (!$statusRepo instanceof WorkflowStateRepositoryInterface) {
            throw new RepositoryException('Repo ":name" must implement :class', [
                ':name'  => get_class($statusRepo),
                ':class' => WorkflowStateRepositoryInterface::class,
            ]);
        }

        $definedActions = $resource->getReservedStatusActionsList();

        $data = [];

        // Use DB records for sanity (detect missing sync between config and DB)
        foreach ($statusRepo->getAll() as $state) {
            $stateName = $state->getCodename();

            // Fetch permissions for all states
            foreach ($this->config->getStateActions($modelName, $stateName) as $action) {
                // Check workflow has not defined unusual actions
                if (!in_array($action, $definedActions, true)) {
                    throw new StatusWorkflowException('Action ":action" is not defined in acl resource for ":model"', [
                        ':action' => $action,
                        ':model'  => $modelName,
                    ]);
                }

                $identity = $resource->makeStatusActionPermissionIdentity($state, $action);

                $data[$identity] = $this->config->getStateActionRoles($modelName, $stateName, $action);

                // Fetch target transitions and add permissions/roles for them
                foreach ($this->config->getStateTargetTransitions($modelName, $stateName) as $transition => $target) {
                    $identity = $resource->makeTransitionPermissionIdentity($state, $transition);

                    $data[$identity] = $this->config->getTransitionRoles($modelName, $transition);
                }
            }
        }

        return $data;
    }
}