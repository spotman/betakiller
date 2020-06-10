<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class Token extends \ORM implements TokenInterface
{
    public const TABLE_NAME     = 'tokens';
    public const COL_USER_ID    = 'user_id';
    public const COL_VALUE      = 'value';
    public const COL_ENDING_AT  = 'ending_at';
    public const COL_CREATED_AT = 'created_at';
    public const COL_USED_AT    = 'used_at';

    public const REL_USER = 'user';

    protected function configure(): void
    {
        $this->_table_name = static::TABLE_NAME;

        $this->belongs_to([
            self::REL_USER => [
                'model'       => User::getModelName(),
                'foreign_key' => self::COL_USER_ID,
            ],
        ]);

        $this->load_with([
            self::REL_USER,
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            self::COL_VALUE => [
                ['not_empty'],
                ['max_length', [':value', 64]],
            ],

            self::COL_CREATED_AT => [
                ['not_empty'],
            ],

            self::COL_ENDING_AT => [
                ['not_empty'],
            ],
        ];
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Model\TokenInterface
     */
    public function setUser(UserInterface $user): TokenInterface
    {
        $this->set(self::REL_USER, $user);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\UserInterface
     */
    public function getUser(): UserInterface
    {
        return $this->getRelatedEntity(self::REL_USER);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\TokenInterface
     */
    public function setValue(string $value): TokenInterface
    {
        $this->set(self::COL_VALUE, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->get(self::COL_VALUE);
    }

    /**
     * @param \DateTimeImmutable $value
     *
     * @return \BetaKiller\Model\TokenInterface
     */
    public function setEndingAt(\DateTimeImmutable $value): TokenInterface
    {
        $this->set_datetime_column_value(self::COL_ENDING_AT, $value);

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getEndingAt(): \DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::COL_ENDING_AT);
    }

    /**
     * @param \DateTimeImmutable $value
     *
     * @return \BetaKiller\Model\TokenInterface
     */
    public function setCreatedAt(\DateTimeImmutable $value): TokenInterface
    {
        $this->set_datetime_column_value(self::COL_CREATED_AT, $value);

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::COL_CREATED_AT);
    }

    /**
     * @param \DateTimeImmutable $value
     *
     * @return \BetaKiller\Model\TokenInterface
     */
    public function setUsedAt(\DateTimeImmutable $value): TokenInterface
    {
        $this->set_datetime_column_value(self::COL_USED_AT, $value);

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getUsedAt(): ?\DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::COL_USED_AT);
    }

    /**
     * @return bool
     */
    public function isUsed(): bool
    {
        return (bool)$this->getUsedAt();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isActive(): bool
    {
        if ($this->isUsed()) {
            return false;
        }

        $endingDate  = $this->getEndingAt();
        $currentDate = new \DateTimeImmutable();

        return ($currentDate < $endingDate);
    }
}
