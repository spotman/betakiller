<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\HasWorkflowStateInterface;
use BetaKiller\Model\UserState;
use BetaKiller\Workflow\WorkflowStateInterface;

/**
 * Interface HasWorkflowStateRepositoryInterface
 *
 * @package BetaKiller\Repository
 * @method HasWorkflowStateInterface[] getAll()
 */
interface HasWorkflowStateRepositoryInterface extends RepositoryInterface
{
    /**
     * @return HasWorkflowStateInterface[]
     */
    public function getAllMissingState(): array;

    /**
     * @param \BetaKiller\Workflow\WorkflowStateInterface $state
     *
     * @return int
     */
    public function countInState(WorkflowStateInterface $state): int;
}
