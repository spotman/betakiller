<?php

declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Workflow\WorkflowStateInterface;

final class UserState extends AbstractWorkflowStateOrmModel implements WorkflowStateInterface
{
    public const TABLE_NAME = 'user_statuses';

    public const COL_ID = 'id';

    public const CREATED   = 'created';       // Just created
    public const BANNED    = 'banned';        // Blocked coz of hacking, spam, or app rules violation
    public const SUSPENDED = 'suspended';     // Account removal requested, so it will be suspended for 6 months
    public const RESUMED   = 'resumed';       // Resumed from suspend, requires additional confirmation (potential fraud)
    public const REMOVED   = 'removed';       // Soft delete (keep ID and email but delete personal data)

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
