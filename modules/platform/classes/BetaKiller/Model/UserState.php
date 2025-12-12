<?php

declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Workflow\WorkflowStateInterface;

class UserState extends AbstractWorkflowStateOrmModel implements WorkflowStateInterface
{
    public const TABLE_NAME = 'user_statuses';

    public const COL_ID = 'id';

    public const CREATED   = 'created';       // Just created
    public const PENDING   = 'pending';       // Waiting for approval (profile is complete)
    public const APPROVED  = 'approved';      // Approved by moderator (or auto-approved on sign-up)
    public const REJECTED  = 'rejected';      // Rejected by moderator
    public const BANNED    = 'banned';        // Blocked coz of hacking, spam, or app rules violation
    public const SUSPENDED = 'suspended';     // Account removal requested, so it will be suspended for 6 months
    public const REMOVED   = 'removed';       // Soft delete (keep ID and email but delete personal data)

    public static function getActiveCodenames(): array
    {
        return [
            self::CREATED,
            self::PENDING,
            self::APPROVED,
        ];
    }

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            self::COL_CODENAME => [
                ['not_empty'],
                ['max_length', [':value', 16]],
            ],
        ];
    }

    /**
     * @return string
     */
    public function getI18nKeyName(): string
    {
        return 'user.status.'.$this->getCodename();
    }
}
