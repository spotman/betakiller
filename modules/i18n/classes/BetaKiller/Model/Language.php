<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class Language extends \ORM implements LanguageInterface
{
    use I18nKeyOrmTrait;

    public const TABLE_NAME             = 'languages';
    public const TABLE_FIELD_ISO_CODE   = 'iso_code';
    public const TABLE_FIELD_LOCALE     = 'locale';
    public const TABLE_FIELD_IS_SYSTEM  = 'is_system';
    public const TABLE_FIELD_IS_DEFAULT = 'is_default';
    public const TABLE_FIELD_I18N       = 'i18n';

    protected function configure(): void
    {
        $this->_table_name = static::TABLE_NAME;
    }

    protected function getI18nValueColumn(): string
    {
        return self::TABLE_FIELD_I18N;
    }

    /**
     * Returns name of I18n key to proceed
     *
     * @return string
     */
    public function getI18nKeyName(): string
    {
        return 'language.'.$this->getIsoCode();
    }

    /**
     * @return bool
     */
    public function isPlural(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return [
            self::TABLE_FIELD_ISO_CODE  => [
                ['not_empty'],
                ['max_length', [':value', 8]],
            ],
            self::TABLE_FIELD_LOCALE    => [
                ['not_empty'],
                ['max_length', [':value', 8]],
            ],
            self::TABLE_FIELD_IS_SYSTEM => [
                ['not_empty'],
                ['max_length', [':value', 1]],
            ],
        ];
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function setIsoCode(string $value): LanguageInterface
    {
        $this->set(self::TABLE_FIELD_ISO_CODE, strtolower($value));

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
     * @param string $value
     *
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function setLocale(string $value): LanguageInterface
    {
        $this->set(self::TABLE_FIELD_LOCALE, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->get(self::TABLE_FIELD_LOCALE);
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->getI18nValue($this) ?: $this->getAnyI18nValue();
    }

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsDefault(): LanguageInterface
    {
        return $this->setIsDefault(true);
    }

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsNonDefault(): LanguageInterface
    {
        return $this->setIsDefault(false);
    }

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsSystem(): LanguageInterface
    {
        return $this->markSystem(true);
    }

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsNonSystem(): LanguageInterface
    {
        return $this->markSystem(false);
    }

    /**
     * @return bool
     */
    public function isSystem(): bool
    {
        return (bool)$this->get(self::TABLE_FIELD_IS_SYSTEM);
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return (bool)$this->get(self::TABLE_FIELD_IS_DEFAULT);
    }

    /**
     * @param bool $value
     *
     * @return \BetaKiller\Model\LanguageInterface
     */
    private function setIsDefault(bool $value): LanguageInterface
    {
        $this->set(self::TABLE_FIELD_IS_DEFAULT, $value);

        return $this;
    }

    /**
     * @param bool $value
     *
     * @return \BetaKiller\Model\LanguageInterface
     */
    private function markSystem(bool $value): LanguageInterface
    {
        $this->set(self::TABLE_FIELD_IS_SYSTEM, (int)$value);

        return $this;
    }
}
