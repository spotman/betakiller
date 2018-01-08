<?php
namespace BetaKiller\Status;

use ORM;

abstract class StatusRelatedModelOrm extends ORM implements StatusRelatedModelInterface
{
    use StatusRelatedModelOrmTrait;

    protected function _initialize()
    {
        $this->initializeRelatedModelRelation();

        parent::_initialize();
    }
}
