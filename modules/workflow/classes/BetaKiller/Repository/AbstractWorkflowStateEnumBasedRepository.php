<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Workflow\WorkflowStateInterface;

/**
 * @method get
 */
abstract class AbstractWorkflowStateEnumBasedRepository extends AbstractEnumBasedDispatchableRepository implements
    WorkflowStateRepositoryInterface, DispatchableRepositoryInterface
{
    /**
     * @param string $codename
     *
     * @return \BetaKiller\Workflow\WorkflowStateInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByCodename(string $codename): WorkflowStateInterface
    {
        return parent::getById($codename);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Workflow\WorkflowStateInterface|null
     */
    public function findByCodename(string $codename): ?WorkflowStateInterface
    {
        return parent::findById($codename);
    }
}
