<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class Token extends \ORM implements TokenInterface
{
    public const TABLE_NAME            = 'tokens';
    public const TABLE_FIELD_USER_ID   = 'user_id';
    public const TABLE_FIELD_VALUE     = 'value';
    public const TABLE_FIELD_ENDING_AT = 'ending_at';

    protected function configure(): void
    {
        $this->_table_name = static::TABLE_NAME;

        $this->belongs_to([
            'user' => [
                'model'       => 'User',
                'foreign_key' => self::TABLE_FIELD_USER_ID,
            ],
        ]);

        $this->load_with([
            'user',
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            self::TABLE_FIELD_VALUE => [
                ['not_empty'],
                ['max_length', [':value', 64]],
            ],
        ];
    }

    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return \BetaKiller\Model\TokenInterface
     */
    public function setUser(UserInterface $userModel): TokenInterface
    {
        $this->set(self::TABLE_FIELD_USER_ID, $userModel);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\UserInterface
     */
    public function getUser(): UserInterface
    {
        return $this->getRelatedEntity('user');
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\TokenInterface
     */
    public function setValue(string $value): TokenInterface
    {
        $this->set(self::TABLE_FIELD_VALUE, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->get(self::TABLE_FIELD_VALUE);
    }

    /**
     * @param \DateTimeImmutable $value
     *
     * @return \BetaKiller\Model\TokenInterface
     */
    public function setEndingAt(\DateTimeImmutable $value): TokenInterface
    {
        $this->set_datetime_column_value(self::TABLE_FIELD_ENDING_AT, $value);

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getEndingAt(): \DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::TABLE_FIELD_ENDING_AT);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isActive(): bool
    {
        $endingDate  = $this->getEndingAt();
        $currentDate = new \DateTimeImmutable();

        return ($currentDate < $endingDate);
    }
}
