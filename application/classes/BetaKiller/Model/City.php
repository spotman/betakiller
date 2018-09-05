<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class City extends \ORM implements CityInterface
{
    public const TABLE_NAME              = 'cities';
    public const TABLE_FIELD_COUNTRY_ID  = 'country_id';
    public const TABLE_FIELD_NAME        = 'name';
    public const TABLE_FIELD_CREATED_AT  = 'created_at';
    public const TABLE_FIELD_CREATED_BY  = 'created_by';
    public const TABLE_FIELD_APPROVED_AT = 'approved_at';
    public const TABLE_FIELD_APPROVED_BY = 'approved_by';
    public const TABLE_FIELD_MAXMIND_ID  = 'maxmind_id';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->belongs_to([
            'country'    => [
                'model'       => 'Country',
                'foreign_key' => self::TABLE_FIELD_COUNTRY_ID,
            ],
            'createdBy'  => [
                'model'       => 'User',
                'foreign_key' => self::TABLE_FIELD_CREATED_BY,
            ],
            'approvedBy' => [
                'model'       => 'User',
                'foreign_key' => self::TABLE_FIELD_APPROVED_BY,
            ],
        ]);
        $this->load_with([
            'country',
            'createdBy',
            'approvedBy',
        ]);

        parent::configure();
    }

    public function rules(): array
    {
        return [
            self::TABLE_FIELD_COUNTRY_ID  => [
                ['not_empty'],
                ['min_length', [':value', 1]],
                ['max_length', [':value', 11]],
            ],
            self::TABLE_FIELD_NAME        => [
                ['not_empty'],
                ['max_length', [':value', 32]],
            ],
            self::TABLE_FIELD_CREATED_BY  => [
                ['not_empty'],
                // todo what rule for digits >0 only?
//                ['digit '],
            ],
            self::TABLE_FIELD_APPROVED_AT => [
                ['date'],
            ],
            self::TABLE_FIELD_APPROVED_BY => [
                // todo what rule for digits >0 only?
                //['digit'],
            ],
        ];
    }

    /**
     * @param \BetaKiller\Model\CountryInterface $countryModel
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setCountry(CountryInterface $countryModel): CityInterface
    {
        $this->set(self::TABLE_FIELD_COUNTRY_ID, $countryModel);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\CountryInterface
     */
    public function getCountry(): CountryInterface
    {
        return $this->get('country');
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setName(string $value): CityInterface
    {
        $value = strtolower(trim($value));
        $this->set(self::TABLE_FIELD_NAME, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->get(self::TABLE_FIELD_NAME);
    }

    /**
     * @param \DateTimeInterface|null $value
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setCreatedAt(?\DateTimeInterface $value = null): CityInterface
    {
        $value = $value ?: new \DateTimeImmutable;
        $this->set_datetime_column_value(self::TABLE_FIELD_CREATED_AT, $value);

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::TABLE_FIELD_CREATED_AT);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setCreatedBy(UserInterface $userModel): CityInterface
    {
        $this->set(self::TABLE_FIELD_CREATED_BY, $userModel);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\UserInterface
     */
    public function getCreatedBy(): UserInterface
    {
        return $this->get('createdBy');
    }

    /**
     * @param \DateTimeInterface|null $value
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setApprovedAt(?\DateTimeInterface $value = null): CityInterface
    {
        $value = $value ?: new \DateTimeImmutable;
        $this->set_datetime_column_value(self::TABLE_FIELD_APPROVED_AT, $value);

        return $this;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getApprovedAt(): ?\DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::TABLE_FIELD_APPROVED_AT);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setApprovedBy(UserInterface $userModel): CityInterface
    {
        $this->set(self::TABLE_FIELD_APPROVED_BY, $userModel);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\UserInterface|null
     */
    public function getApprovedBy(): ?UserInterface
    {
        return $this->get('approvedBy');
    }

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setMaxmindId(int $value): CityInterface
    {
        $this->set(self::TABLE_FIELD_MAXMIND_ID, $value);

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxmindId(): int
    {
        return $this->get(self::TABLE_FIELD_MAXMIND_ID);
    }
}
