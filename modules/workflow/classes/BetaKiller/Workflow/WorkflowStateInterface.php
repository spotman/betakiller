<?php
namespace BetaKiller\Workflow;

use BetaKiller\Model\AbstractEntityInterface;
use BetaKiller\Model\HasI18nKeyNameInterface;

interface WorkflowStateInterface extends HasI18nKeyNameInterface
{
    /**
     * @return string
     */
    public function getCodename(): string;
}
