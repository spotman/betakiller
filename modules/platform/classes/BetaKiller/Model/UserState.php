<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Workflow\AbstractWorkflowStateOrm;

final class UserState extends AbstractWorkflowStateOrm implements UserStateInterface
{
    public const TABLE_NAME = 'user_statuses';

    public const CREATED         = 'created';       // Just created
    public const EMAIL_CONFIRMED = 'confirmed';     // Email confirmed
    public const EMAIL_CHANGED   = 'email-changed'; // Email changed
    public const BLOCKED         = 'blocked';       // Blocked coz of hacking, spam, or app rules violation
    public const SUSPENDED       = 'suspended';     // Account removal requested so it will be suspended for 6 months
    public const RESUMED         = 'resumed';       // Resumed from suspend, requires additional confirmation (potential fraud)

    public const ACTIVE_STATES = [
        self::EMAIL_CONFIRMED,
        self::EMAIL_CHANGED,
    ];

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

    /**
     * @return bool
     * @throws \BetaKiller\Exception\DomainException
     */
    public function isCreated(): bool
    {
        return $this->isWorkflowStateCodename(self::CREATED);
    }

    /**
     * @return bool
     * @throws \BetaKiller\Exception\DomainException
     */
    public function isConfirmed(): bool
    {
        return $this->isWorkflowStateCodename(self::EMAIL_CONFIRMED);
    }

    /**
     * @inheritDoc
     */
    public function isEmailChanged(): bool
    {
        return $this->isWorkflowStateCodename(self::EMAIL_CHANGED);
    }

    /**
     * @return bool
     * @throws \BetaKiller\Exception\DomainException
     */
    public function isBlocked(): bool
    {
        return $this->isWorkflowStateCodename(self::BLOCKED);
    }

    /**
     * @return bool
     * @throws \BetaKiller\Exception\DomainException
     */
    public function isSuspended(): bool
    {
        return $this->isWorkflowStateCodename(self::SUSPENDED);
    }

    /**
     * @return bool
     */
    public function isResumed(): bool
    {
        return $this->isWorkflowStateCodename(self::RESUMED);
    }
}
