<?php
namespace BetaKiller\Workflow;

use ORM;

abstract class HasWorkflowStateModelOrm extends ORM implements HasWorkflowStateInterface
{
    use HasWorkflowStateModelOrmTrait;

    protected function configure(): void
    {
        $this->configureWorkflowStateRelation();
    }
}
