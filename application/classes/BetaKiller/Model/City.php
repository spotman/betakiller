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

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->belongs_to([
            'country' => [
                'model'       => 'Country',
                'foreign_key' => self::TABLE_FIELD_COUNTRY_ID,
            ],
        ]);

        $this->load_with([
            'country',
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
     * @param int $value
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setCountryId(int $value): CityInterface
    {
        $this->set(self::TABLE_FIELD_COUNTRY_ID, $value);

        return $this;
    }

    /**
     * @return int
     */
    public function getCountryId(): int
    {
        return (int)$this->get(self::TABLE_FIELD_COUNTRY_ID);
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
     * @param int $value
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setCreatedBy(int $value): CityInterface
    {
        $this->set(self::TABLE_FIELD_CREATED_BY, $value);

        return $this;
    }

    /**
     * @return int
     */
    public function getCreatedBy(): int
    {
        return (int)$this->get(self::TABLE_FIELD_CREATED_BY);
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
     * @param int $value
     *
     * @return \BetaKiller\Model\CityInterface
     */
    public function setApprovedBy(int $value): CityInterface
    {
        $this->set(self::TABLE_FIELD_APPROVED_BY, $value);

        return $this;
    }

    /**
     * @return int
     */
    public function getApprovedBy(): int
    {
        return (int)$this->get(self::TABLE_FIELD_APPROVED_BY);
    }

    /**
     * @return \BetaKiller\Model\CountryInterface
     */
    public function getCountry(): CountryInterface
    {
        return $this->get('country');
    }
}
