<?php
namespace BetaKiller\Workflow;

use BetaKiller\Model\AbstractEntityInterface;

interface HasWorkflowStateWithHistoryInterface extends HasWorkflowStateInterface
{
    /**
     * @return string
     */
    public static function getWorkflowStateHistoryModelName(): string;
}
