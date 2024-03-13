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

        $rel = $this->getStateRelationKey();
        $col = $this->getStateCodenameColumnName();

        $orm->where($rel.'.'.$col, 'IS', null);

        return $this->findAll($orm);
    }

    protected function filterState(OrmInterface $orm, WorkflowStateInterface $state): self
    {
        return $this->filterStateCodename($orm, $state->getCodename());
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param WorkflowStateInterface[]                  $states
     *
     * @return $this
     */
    protected function filterStates(OrmInterface $orm, array $states): self
    {
        $codenames = array_map(static function (WorkflowStateInterface $state) {
            return $state->getCodename();
        }, $states);

        return $this->filterStatesCodenames($orm, $codenames);
    }

    protected function filterStateCodename(OrmInterface $orm, string $codename): self
    {
        $rel = $this->getStateRelationKey();
        $col = $this->getStateCodenameColumnName();

        $orm->where($rel.'.'.$col, '=', $codename);

        return $this;
    }

    protected function filterStatesCodenames(OrmInterface $orm, array $codenames, bool $not = null): self
    {
        $rel = $this->getStateRelationKey();
        $col = $this->getStateCodenameColumnName();

        $orm->where($rel.'.'.$col, $not ? 'NOT IN' : 'IN', $codenames);

        return $this;
    }

    abstract protected function getStateRelationKey(): string;

    abstract protected function getStateCodenameColumnName(): string;
}
