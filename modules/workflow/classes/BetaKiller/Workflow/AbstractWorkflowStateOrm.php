<?php
namespace BetaKiller\Workflow;

use ORM;

abstract class AbstractWorkflowStateOrm extends ORM implements WorkflowStateInterface
{
    public const COL_CODENAME  = 'codename';
    public const COL_IS_START  = 'is_start';
    public const COL_IS_FINISH = 'is_finish';

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        // Nothing to do here
    }

    /**
     * @return string
     */
    public function getCodename(): string
    {
        return (string)$this->get(self::COL_CODENAME);
    }

    /**
     * @param string $value
     *
     * @return void
     */
    public function setCodename(string $value): void
    {
        $this->set(self::COL_CODENAME, $value);
    }

    /**
     *
     */
    public function markAsStart(): void
    {
        $this->set(self::COL_IS_START, true);
        $this->set(self::COL_IS_FINISH, false);
    }

    /**
     *
     */
    public function markAsFinish(): void
    {
        $this->set(self::COL_IS_START, false);
        $this->set(self::COL_IS_FINISH, true);
    }

    /**
     *
     */
    public function markAsRegular(): void
    {
        $this->set(self::COL_IS_START, false);
        $this->set(self::COL_IS_FINISH, false);
    }

    public function isStart(): bool
    {
        return (bool)$this->get(self::COL_IS_START);
    }

    public function isFinish(): bool
    {
        return (bool)$this->get(self::COL_IS_FINISH);
    }
}
