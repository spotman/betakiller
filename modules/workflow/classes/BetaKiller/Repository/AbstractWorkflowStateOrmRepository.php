<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use BetaKiller\Model\AbstractWorkflowStateOrmModel;
use BetaKiller\Workflow\WorkflowStateInterface;

abstract class AbstractWorkflowStateOrmRepository extends AbstractOrmBasedDispatchableRepository implements
    WorkflowStateDbRepositoryInterface
{
    /**
     * @return \BetaKiller\Workflow\WorkflowStateInterface
     */
    public function getStartState(): WorkflowStateInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterIsStart($orm)
            ->getOne($orm);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Workflow\WorkflowStateInterface|mixed
     */
    public function getByCodename(string $codename): WorkflowStateInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterCodename($orm, $codename)
            ->getOne($orm);
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Workflow\WorkflowStateInterface|mixed|null
     */
    public function findByCodename(string $codename): ?WorkflowStateInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterCodename($orm, $codename)
            ->findOne($orm);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string[]                                  $codenames
     *
     * @param bool|null                                 $order
     *
     * @return $this
     */
    protected function filterCodenames(OrmInterface $orm, array $codenames, bool $order = null): self
    {
        $col = $orm->object_column(AbstractWorkflowStateOrmModel::COL_CODENAME);

        $orm->where($orm->object_column(AbstractWorkflowStateOrmModel::COL_CODENAME), 'IN', $codenames);

        if ($order) {
            $orm->order_by_field_sequence($col, $codenames);
        }

        return $this;
    }

    private function filterCodename(OrmInterface $orm, string $codename): self
    {
        $orm->where($orm->object_column(AbstractWorkflowStateOrmModel::COL_CODENAME), '=', $codename);

        return $this;
    }

    private function filterIsStart(OrmInterface $orm): self
    {
        $orm->where($orm->object_column(AbstractWorkflowStateOrmModel::COL_IS_START), '=', true);

        return $this;
    }
}
