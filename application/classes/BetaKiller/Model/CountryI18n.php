<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class CountryI18n extends \ORM implements CountryI18nInterface
{
    public const TABLE_NAME              = 'countries_i18n';
    public const TABLE_FIELD_COUNTRY_ID  = 'country_id';
    public const TABLE_FIELD_LANGUAGE_ID = 'language_id';
    public const TABLE_FIELD_VALUE       = 'value';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

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
            self::TABLE_FIELD_LANGUAGE_ID => [
                ['not_empty'],
                ['min_length', [':value', 1]],
                ['max_length', [':value', 11]],
            ],
            self::TABLE_FIELD_VALUE       => [
                ['not_empty'],
            ],
        ];
    }

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\CountryI18nInterface
     */
    public function setCountryId(int $value): CountryI18nInterface
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
     * @param int $value
     *
     * @return \BetaKiller\Model\CountryI18nInterface
     */
    public function setLanguageId(int $value): CountryI18nInterface
    {
        $this->set(self::TABLE_FIELD_LANGUAGE_ID, $value);

        return $this;
    }

    /**
     * @return int
     */
    public function getLanguageId(): int
    {
        return (int)$this->get(self::TABLE_FIELD_LANGUAGE_ID);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\CountryI18nInterface
     */
    public function setValue(string $value): CountryI18nInterface
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
}
