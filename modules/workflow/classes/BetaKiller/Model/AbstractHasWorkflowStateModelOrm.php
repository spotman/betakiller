<?php
namespace BetaKiller\Model;

use ORM;

abstract class AbstractHasWorkflowStateModelOrm extends ORM implements HasWorkflowStateInterface
{
    use HasWorkflowStateOrmModelTrait;

    protected function configure(): void
    {
        $this->configureWorkflowStateRelation();
    }
}
