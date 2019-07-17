<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Workflow\WorkflowStateInterface;

/**
 * Interface WorkflowStateRepositoryInterface
 *
 * @package BetaKiller\Repository
 * @method WorkflowStateInterface[] getAll()
 * @method save(WorkflowStateInterface $entity)
 */
interface WorkflowStateRepositoryInterface extends RepositoryInterface
{
    /**
     * @return \BetaKiller\Workflow\WorkflowStateInterface
     */
    public function getStartState(): WorkflowStateInterface;

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Workflow\WorkflowStateInterface
     */
    public function getByCodename(string $codename): WorkflowStateInterface;

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Workflow\WorkflowStateInterface|null
     */
    public function findByCodename(string $codename): ?WorkflowStateInterface;
}
