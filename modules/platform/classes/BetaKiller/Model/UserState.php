<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Workflow\AbstractWorkflowStateOrm;

final class UserState extends AbstractWorkflowStateOrm implements UserStateInterface
{
    public const TABLE_NAME = 'user_statuses';

    public const STATE_CREATED         = 'created';      // Just created
    public const STATE_EMAIL_CONFIRMED = 'confirmed';    // Email confirmed
    public const STATE_EMAIL_CHANGED   = 'email-changed';    // Email changed
    public const STATE_BLOCKED         = 'blocked';      // Blocked coz of hacking, spam, or app rules violation
    public const STATE_CLAIMED         = 'claimed';      // User claimed about registration
    public const STATE_SUSPENDED       = 'suspended';    // Account removal requested so it will be suspended for 6 months

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
        return $this->isWorkflowStateCodename(self::STATE_CREATED);
    }

    /**
     * @return bool
     * @throws \BetaKiller\Exception\DomainException
     */
    public function isConfirmed(): bool
    {
        return $this->isWorkflowStateCodename(self::STATE_EMAIL_CONFIRMED);
    }

    /**
     * @return bool
     * @throws \BetaKiller\Exception\DomainException
     */
    public function isBlocked(): bool
    {
        return $this->isWorkflowStateCodename(self::STATE_BLOCKED);
    }

    /**
     * @return bool
     */
    public function isClaimed(): bool
    {
        return $this->isWorkflowStateCodename(self::STATE_CLAIMED);
    }

    /**
     * @return bool
     * @throws \BetaKiller\Exception\DomainException
     */
    public function isSuspended(): bool
    {
        return $this->isWorkflowStateCodename(self::STATE_SUSPENDED);
    }
}
