<?php
namespace BetaKiller\Model;

interface HasWorkflowStateWithHistoryInterface extends HasWorkflowStateInterface
{
    /**
     * @return string
     */
    public static function getWorkflowStateHistoryModelName(): string;
}
