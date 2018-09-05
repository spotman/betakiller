<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class Country extends \ORM implements CountryInterface
{
    public const TABLE_NAME              = 'countries';
    public const TABLE_FIELD_ISO_CODE    = 'iso_code';
    public const TABLE_FIELD_CREATED_AT  = 'created_at';
    public const TABLE_FIELD_CREATED_BY  = 'created_by';
    public const TABLE_FIELD_APPROVED_AT = 'approved_at';
    public const TABLE_FIELD_APPROVED_BY = 'approved_by';
    public const TABLE_FIELD_EU          = 'is_eu';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->belongs_to([
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
            'createdBy',
            'approvedBy',
        ]);

        parent::configure();
    }

    public function rules(): array
    {
        return [
            self::TABLE_FIELD_ISO_CODE    => [
                ['not_empty'],
                ['min_length', [':value', 2]],
                ['max_length', [':value', 2]],
                ['alpha'],
            ],
            self::TABLE_FIELD_CREATED_AT  => [
                ['not_empty'],
                ['date'],
            ],
            self::TABLE_FIELD_CREATED_BY  => [
                ['not_empty'],
                // todo what rule for digits >0 only?
                //['digit '],
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
     * @param string $value
     *
     * @return \BetaKiller\Model\CountryInterface
     */
    public function setIsoCode(string $value): CountryInterface
    {
        $value = strtoupper(trim($value));
        $this->set(self::TABLE_FIELD_ISO_CODE, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getIsoCode(): string
    {
        return $this->get(self::TABLE_FIELD_ISO_CODE);
    }

    /**
     * @param \DateTimeInterface|null $value
     *
     * @return \BetaKiller\Model\CountryInterface
     */
    public function setCreatedAt(?\DateTimeInterface $value = null): CountryInterface
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
     * @return \BetaKiller\Model\CountryInterface
     */
    public function setCreatedBy(UserInterface $userModel): CountryInterface
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
     * @return \BetaKiller\Model\CountryInterface
     */
    public function setApprovedAt(?\DateTimeInterface $value = null): CountryInterface
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
     * @return \BetaKiller\Model\CountryInterface
     */
    public function setApprovedBy(UserInterface $userModel): CountryInterface
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
     * @param bool $value
     *
     * @return \BetaKiller\Model\CountryInterface
     */
    public function setEuStatus(bool $value): CountryInterface
    {
        $this->set(self::TABLE_FIELD_EU, $value);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\CountryInterface
     */
    public function enableEu(): CountryInterface
    {
        $this->set(self::TABLE_FIELD_EU, true);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\CountryInterface
     */
    public function disableEu(): CountryInterface
    {
        $this->set(self::TABLE_FIELD_EU, false);

        return $this;
    }

    /**
     * @return bool
     */
    public function getEuStatus(): bool
    {
        return $this->get(self::TABLE_FIELD_EU);
    }
}
