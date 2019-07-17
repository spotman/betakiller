<?php
namespace BetaKiller\Workflow;

use ORM;

abstract class HasWorkflowStateModelOrm extends ORM implements HasWorkflowStateModelInterface
{
    use HasWorkflowStateModelOrmTrait;

    protected function configure(): void
    {
        $this->configureWorkflowModelRelation();
    }
}
