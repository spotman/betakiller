<?php
namespace BetaKiller\Acl\Resource;

use BetaKiller\Workflow\WorkflowStateInterface;

interface HasWorkflowStateAclResourceInterface extends EntityRelatedAclResourceInterface
{
    /**
     * @return string[]
     */
    public function getStateRelatedActionsList(): array;

    /**
     * @param \BetaKiller\Workflow\WorkflowStateInterface $state
     * @param string                                      $action
     *
     * @return bool
     */
    public function isStateActionAllowed(WorkflowStateInterface $state, string $action): bool;

    /**
     * @param \BetaKiller\Workflow\WorkflowStateInterface $state
     * @param string                                      $transition
     *
     * @return bool
     */
    public function isStatusTransitionAllowed(WorkflowStateInterface $state, string $transition): bool;

    /**
     * @param \BetaKiller\Workflow\WorkflowStateInterface $state
     * @param string                                      $action
     *
     * @return string
     */
    public function makeStatusActionPermissionIdentity(WorkflowStateInterface $state, string $action): string;

    /**
     * @param \BetaKiller\Workflow\WorkflowStateInterface $state
     * @param string                                      $transition
     *
     * @return string
     */
    public function makeTransitionPermissionIdentity(WorkflowStateInterface $state, string $transition): string;
}
