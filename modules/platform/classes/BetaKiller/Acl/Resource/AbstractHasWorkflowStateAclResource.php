<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Workflow\HasWorkflowStateModelInterface;
use BetaKiller\Workflow\WorkflowStateException;
use BetaKiller\Workflow\WorkflowStateInterface;

abstract class AbstractHasWorkflowStateAclResource extends AbstractEntityRelatedAclResource implements
    HasWorkflowStateAclResourceInterface
{
    /**
     * Provides array of roles` names which are allowed to create entities
     *
     * @return string[]
     */
    abstract protected function getCreatePermissionRoles(): array;

    /**
     * Provides array of roles` names which are allowed to browse(list) entities
     *
     * @return string[]
     */
    abstract protected function getListPermissionRoles(): array;

    /**
     * Provides array of roles` names which are allowed to search for entities
     *
     * @return string[]
     */
    abstract protected function getSearchPermissionRoles(): array;

    /**
     * Returns default permissions bundled with current resource
     * Key=>Value pairs where key is a permission identity and value is an array of roles
     * Useful for presetting permissions for resources with fixed access control list or permissions based on hard-coded logic
     *
     * @return string[][]
     */
    final public function getDefaultAccessList(): array
    {
        return array_merge($this->getAdditionalAccessList(), [
            self::ACTION_CREATE => $this->getCreatePermissionRoles(),
            self::ACTION_LIST   => $this->getListPermissionRoles(),
            self::ACTION_SEARCH => $this->getSearchPermissionRoles(),
        ]);
    }

    /**
     * @param string $permissionIdentity
     *
     * @return bool
     */
    public function isPermissionAllowed(string $permissionIdentity): bool
    {
        // Read/Update/Delete permissions rely on model status permissions
        if (in_array($permissionIdentity, $this->getReservedStatusActionsList(), true)) {
            $state = $this->getCurrentState();

            return $this->isStateActionAllowed($state, $permissionIdentity);
        }

        // Permissions defined in default access list have default logic
        if ($this->isPermissionDefined($permissionIdentity)) {
            return parent::isPermissionAllowed($permissionIdentity);
        }

        // Other permissions rely on model status transition permissions
        $state = $this->getCurrentState();

        return $this->isStatusTransitionAllowed($state, $permissionIdentity);
    }

    /**
     * @param \BetaKiller\Workflow\WorkflowStateInterface $model
     * @param string                                      $action
     *
     * @return bool
     */
    public function isStateActionAllowed(WorkflowStateInterface $model, string $action): bool
    {
        $identity = $this->makeStatusActionPermissionIdentity($model, $action);

        return parent::isPermissionAllowed($identity);
    }

    public function isStatusTransitionAllowed(WorkflowStateInterface $state, string $transitionName): bool
    {
        $identity = $this->makeTransitionPermissionIdentity($state, $transitionName);

        return parent::isPermissionAllowed($identity);
    }

    /**
     * @param \BetaKiller\Workflow\WorkflowStateInterface $state
     * @param string                                      $action
     *
     * @return string
     */
    public function makeStatusActionPermissionIdentity(WorkflowStateInterface $state, string $action): string
    {
        return 'status.'.$state->getCodename().'.action.'.$action;
    }

    public function makeTransitionPermissionIdentity(WorkflowStateInterface $state, string $transition): string
    {
        return 'status.'.$state->getCodename().'.transition.'.$transition;
    }

    /**
     * @return string[]
     */
    public function getReservedStatusActionsList(): array
    {
        return [
            self::ACTION_READ,
            self::ACTION_UPDATE,
            self::ACTION_DELETE,
        ];
    }

    /**
     * Returns true if this resource needs custom permission collector
     *
     * @return bool
     */
    public function isCustomRulesCollectorUsed(): bool
    {
        return true;
    }

    protected function getAdditionalAccessList(): array
    {
        return [];
    }

    private function getCurrentState(): WorkflowStateInterface
    {
        $entity = $this->getEntity();

        if (!$entity instanceof HasWorkflowStateModelInterface) {
            throw new WorkflowStateException('Entity ":name" must implement :class', [
                ':name'  => $entity::getModelName(),
                ':class' => HasWorkflowStateModelInterface::class,
            ]);
        }

        return $entity->getWorkflowState();
    }
}
