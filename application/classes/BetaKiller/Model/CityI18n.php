<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class CityI18n extends \ORM implements CityI18nInterface
{
    public const TABLE_NAME              = 'cities_i18n';
    public const TABLE_FIELD_CITY_ID     = 'city_id';
    public const TABLE_FIELD_LANGUAGE_ID = 'language_id';
    public const TABLE_FIELD_VALUE       = 'value';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->belongs_to([
            'city'     => [
                'model'       => 'City',
                'foreign_key' => self::TABLE_FIELD_CITY_ID,
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
            self::TABLE_FIELD_CITY_ID     => [
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
     * @param \BetaKiller\Model\CityInterface $cityModel
     *
     * @return \BetaKiller\Model\CityI18nInterface
     */
    public function setCity(CityInterface $cityModel): CityI18nInterface
    {
        $this->set(self::TABLE_FIELD_CITY_ID, $cityModel);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\CityInterface
     */
    public function getCity(): CityInterface
    {
        return $this->get('city');
    }

    /**
     * @param \BetaKiller\Model\LanguageInterface $languageModel
     *
     * @return \BetaKiller\Model\CityI18nInterface
     */
    public function setLanguage(LanguageInterface $languageModel): CityI18nInterface
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
     * @return \BetaKiller\Model\CityI18nInterface
     */
    public function setValue(string $value): CityI18nInterface
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
