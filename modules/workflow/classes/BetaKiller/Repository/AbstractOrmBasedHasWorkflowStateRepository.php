<?php

declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Workflow\WorkflowStateInterface;

abstract class AbstractOrmBasedHasWorkflowStateRepository extends AbstractHasWorkflowStateRepository
{
    protected function getStateColumnName(): string
    {
        $rel = $this->getStateRelationKey();
        $col = $this->getStateCodenameColumnName();

        return \ORM::col($rel, $col);
    }

    protected function getStateColumnValue(WorkflowStateInterface $state): int|string
    {
        return $state->getCodename();
    }

    abstract protected function getStateRelationKey(): string;

    abstract protected function getStateCodenameColumnName(): string;
}
