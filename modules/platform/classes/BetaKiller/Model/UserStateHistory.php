<?php

declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Workflow\WorkflowStateInterface;

class UserStateHistory extends AbstractWorkflowStateHistoryOrmModel
{
    public const TABLE_NAME    = 'user_status_history';
    public const COL_ID        = 'id';
    public const COL_STATUS_ID = 'status_id';
    public const REL_STATE     = 'state';

    protected function configure(): void
    {
        $this->_table_name  = self::TABLE_NAME;
        $this->_primary_key = self::COL_ID;

        parent::configure();

        $this->belongs_to([
            self::REL_STATE => [
                'model'       => UserState::getModelName(),
                'foreign_key' => self::COL_STATUS_ID,
            ],
        ]);

        $this->load_with([
            self::REL_STATE,
        ]);
    }

    public static function getEntityModelName(): string
    {
        return User::getModelName();
    }

    public static function getEntityForeignKey(): string
    {
        return 'user_id';
    }

    public function getState(): WorkflowStateInterface
    {
        return $this->getRelatedEntity(self::REL_STATE);
    }

    protected function bindToState(WorkflowStateInterface $state): static
    {
        $this->setOnce(self::REL_STATE, $state);

        return $this;
    }
}
