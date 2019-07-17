<?php
namespace BetaKiller\Workflow;

use BetaKiller\Model\AbstractEntityInterface;

interface WorkflowStateInterface extends AbstractEntityInterface
{
    /**
     * @param string $codename
     *
     * @return void
     */
    public function setCodename(string $codename): void;

    /**
     * @return string
     */
    public function getCodename(): string;

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
