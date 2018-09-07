<?php
declare(strict_types=1);

namespace BetaKiller\Model;

abstract class AbstractI18nModel extends \ORM implements I18nInterface
{
    public const TABLE_NAME              = '';
    public const TABLE_FIELD_TARGET_ID   = '';
    public const TABLE_FIELD_LANGUAGE_ID = 'language_id';
    public const TABLE_FIELD_VALUE       = 'value';
    public const TARGET_MODEL            = '';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->belongs_to([
            self::TARGET_MODEL => [
                'model'       => self::TARGET_MODEL,
                'foreign_key' => self::TABLE_FIELD_TARGET_ID,
            ],
            'language'         => [
                'model'       => 'Language',
                'foreign_key' => self::TABLE_FIELD_LANGUAGE_ID,
            ],
        ]);
        $this->load_with([
            self::TARGET_MODEL,
            'language',
        ]);

        parent::configure();
    }

    public function rules(): array
    {
        return [
            self::TABLE_FIELD_TARGET_ID   => [
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
     * @param \BetaKiller\Model\LanguageInterface $languageModel
     *
     * @return \BetaKiller\Model\I18nInterface
     */
    public function setLanguage(LanguageInterface $languageModel): I18nInterface
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
     * @return \BetaKiller\Model\I18nInterface
     */
    public function setValue(string $value): I18nInterface
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
