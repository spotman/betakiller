<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\DomainException;

class UserStatus extends \ORM implements UserStatusInterface
{
    public const TABLE_NAME           = 'user_statuses';
    public const TABLE_FIELD_CODENAME = 'codename';

    public const STATUS_CREATED   = 'created';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_VERIFIED  = 'verified';
    public const STATUS_BLOCKED   = 'blocked';

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
            self::TABLE_FIELD_CODENAME => [
                ['not_empty'],
                ['max_length', [':value', 16]],
            ],
        ];
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserStatusInterface
     */
    public function setCodename(string $value): UserStatusInterface
    {
        return $this->set(self::TABLE_FIELD_CODENAME, $value);
    }

    /**
     * @return string
     */
    public function getCodename(): string
    {
        return $this->get(self::TABLE_FIELD_CODENAME);
    }

    /**
     * @return string
     */
    public function getLabelI18nKey(): string
    {
        return 'account.status.'.$this->getCodename();
    }

    /**
     * @param string $value
     *
     * @return bool
     * @throws \BetaKiller\Exception\DomainException
     */
    public function isStatus(string $value): bool
    {
        if (!$this->getCodename()) {
            throw new DomainException('Status codename is empty');
        }

        return $this->getCodename() === $value;
    }

    /**
     * @return bool
     * @throws \BetaKiller\Exception\DomainException
     */
    public function isCreated(): bool
    {
        return $this->isStatus(self::STATUS_CREATED);
    }

    /**
     * @return bool
     * @throws \BetaKiller\Exception\DomainException
     */
    public function isApproved(): bool
    {
        return $this->isStatus(self::STATUS_APPROVED);
    }

    /**
     * @return bool
     * @throws \BetaKiller\Exception\DomainException
     */
    public function isVerified(): bool
    {
        return $this->isStatus(self::STATUS_VERIFIED);
    }

    /**
     * @return bool
     * @throws \BetaKiller\Exception\DomainException
     */
    public function isBlocked(): bool
    {
        return $this->isStatus(self::STATUS_BLOCKED);
    }
}
