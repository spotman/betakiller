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
                $allowedStates[] = $state->getID();
            }
        }

        return $allowedStates;
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
