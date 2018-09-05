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

        $this->belongs_to([
            'country'  => [
                'model'       => 'Country',
                'foreign_key' => self::TABLE_FIELD_COUNTRY_ID,
            ],
            'language' => [
                'model'       => 'Language',
                'foreign_key' => self::TABLE_FIELD_LANGUAGE_ID,
            ],
        ]);
        $this->load_with([
            'city',
            'language',
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
     * @param \BetaKiller\Model\CountryInterface $countryModel
     *
     * @return \BetaKiller\Model\CountryI18nInterface
     */
    public function setCountry(CountryInterface $countryModel): CountryI18nInterface
    {
        $this->set(self::TABLE_FIELD_COUNTRY_ID, $countryModel);

        return $this;
    }

    /**
     * @return CountryInterface
     */
    public function getCountry(): CountryInterface
    {
        return $this->get('country');
    }

    /**
     * @param \BetaKiller\Model\LanguageInterface $languageModel
     *
     * @return \BetaKiller\Model\CountryI18nInterface
     */
    public function setLanguage(LanguageInterface $languageModel): CountryI18nInterface
    {
        $this->set(self::TABLE_FIELD_LANGUAGE_ID, $languageModel);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function getLanguage(): LanguageInterface
    {
        return $this->get('language');
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
