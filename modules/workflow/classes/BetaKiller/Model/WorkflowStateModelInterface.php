<?php
namespace BetaKiller\Model;

use BetaKiller\Workflow\WorkflowStateInterface;

interface WorkflowStateModelInterface extends AbstractEntityInterface, WorkflowStateInterface
{
    /**
     * @param string $codename
     *
     * @return void
     */
    public function setCodename(string $codename): void;

    /**
     *
     */
    public function markAsStart(): void;

    /**
     *
     */
    public function markAsFinish(): void;

    /**
     *
     */
    public function markAsRegular(): void;

    /**
     * @return bool
     */
    public function isStart(): bool;

    /**
     * @return bool
     */
    public function isFinish(): bool;
}
