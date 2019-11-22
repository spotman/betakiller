<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Acl\Resource\HasWorkflowStateAclResourceInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use BetaKiller\Workflow\AbstractWorkflowStateOrm;
use BetaKiller\Workflow\WorkflowStateInterface;

abstract class AbstractWorkflowStateRepository extends AbstractOrmBasedDispatchableRepository implements
    WorkflowStateRepositoryInterface
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
     * @return \BetaKiller\Workflow\WorkflowStateInterface
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
     * @return \BetaKiller\Workflow\WorkflowStateInterface|null
     */
    public function findByCodename(string $codename): ?WorkflowStateInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterCodename($orm, $codename)
            ->findOne($orm);
    }

    /**
     * @param \BetaKiller\Acl\Resource\HasWorkflowStateAclResourceInterface $resource
     * @param string|null                                                   $action
     *
     * @return \BetaKiller\Workflow\WorkflowStateInterface[]
     */
    public function getAllowedStates(HasWorkflowStateAclResourceInterface $resource, string $action = null): array
    {
        if (!$action) {
            $action = $resource::ACTION_READ;
        }

        $allowedStates = [];

        foreach ($this->getAll() as $state) {
            if ($resource->isStateActionAllowed($state, $action)) {
                $allowedStates[] = $state->getCodename();
            }
        }

        return $allowedStates;
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
        $col = $orm->object_column(AbstractWorkflowStateOrm::COL_CODENAME);

        $orm->where($orm->object_column(AbstractWorkflowStateOrm::COL_CODENAME), 'IN', $codenames);

        if ($order) {
            $orm->order_by_field_sequence($col, $codenames);
        }

        return $this;
    }

    private function filterCodename(OrmInterface $orm, string $codename): self
    {
        $orm->where($orm->object_column(AbstractWorkflowStateOrm::COL_CODENAME), '=', $codename);

        return $this;
    }

    private function filterIsStart(OrmInterface $orm): self
    {
        $orm->where($orm->object_column(AbstractWorkflowStateOrm::COL_IS_START), '=', true);

        return $this;
    }
}
