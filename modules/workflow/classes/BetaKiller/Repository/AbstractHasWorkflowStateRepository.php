<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use BetaKiller\Workflow\WorkflowStateInterface;

abstract class AbstractHasWorkflowStateRepository extends AbstractOrmBasedDispatchableRepository implements
    HasWorkflowStateRepositoryInterface
{
    protected function filterState(OrmInterface $orm, WorkflowStateInterface $state): self
    {
        return $this->filterStateCodename($orm, $state->getCodename());
    }

    protected function filterStateCodename(OrmInterface $orm, string $codename): self
    {
        $rel = $this->getStateRelationKey();
        $col = $this->getStateCodenameColumnName();

        $orm->where($rel.'.'.$col, '=', $codename);

        return $this;
    }

    abstract protected function getStateRelationKey(): string;

    abstract protected function getStateCodenameColumnName(): string;
}
