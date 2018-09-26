<?php
namespace BetaKiller\Status;

use ORM;

abstract class StatusRelatedModelOrm extends ORM implements StatusRelatedModelInterface
{
    use StatusRelatedModelOrmTrait;

    protected function configure(): void
    {
        $this->initializeRelatedModelRelation();
    }
}
