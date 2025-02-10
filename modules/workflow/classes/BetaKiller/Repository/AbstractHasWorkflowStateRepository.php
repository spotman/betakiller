<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use BetaKiller\Model\HasWorkflowStateInterface;
use BetaKiller\Workflow\WorkflowStateInterface;

abstract class AbstractHasWorkflowStateRepository extends AbstractOrmBasedDispatchableRepository implements
    HasWorkflowStateRepositoryInterface
{
    /**
     * @return HasWorkflowStateInterface[]
     */
    public function getAllMissingState(): array
    {
        $orm = $this->getOrmInstance();

        $col = $this->getStateColumnName();

        $orm->where($col, 'IS', null);

        return $this->findAll($orm);
    }

    protected function filterState(OrmInterface $orm, WorkflowStateInterface $state): self
    {
        $orm->where($this->getStateColumnName(), '=', $this->getStateColumnValue($state));

        return $this;    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param WorkflowStateInterface[]                  $states
     * @param bool|null                                 $not
     *
     * @return $this
     */
    protected function filterStates(OrmInterface $orm, array $states, bool $not = null): self
    {
        $values = array_map(fn (WorkflowStateInterface $state) => $this->getStateColumnValue($state), $states);

        $col = $this->getStateColumnName();

        $orm->where($col, $not ? 'NOT IN' : 'IN', $values);

        return $this;
    }

    abstract protected function getStateColumnName(): string;

    abstract protected function getStateColumnValue(WorkflowStateInterface $state): int|string;
}
