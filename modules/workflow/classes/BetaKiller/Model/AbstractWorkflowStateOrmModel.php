<?php
namespace BetaKiller\Model;

use BetaKiller\Exception\DomainException;
use ORM;

abstract class AbstractWorkflowStateOrmModel extends ORM implements WorkflowStateModelInterface
{
    public const COL_CODENAME  = 'codename';
    public const COL_IS_START  = 'is_start';
    public const COL_IS_FINISH = 'is_finish';

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

    /**
     * @param string $value
     *
     * @return bool
     * @throws \BetaKiller\Exception\DomainException
     */
    protected function isWorkflowStateCodename(string $value): bool
    {
        $codename = $this->getCodename();

        if (empty($codename)) {
            throw new DomainException('Workflow state ":name" codename is empty', [
                ':name' => $this::getModelName(),
            ]);
        }

        return $codename === $value;
    }
}
