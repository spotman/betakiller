<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class Language extends \ORM implements LanguageInterface
{
    use I18nKeyOrmTrait;

    public const TABLE_NAME             = 'languages';
    public const TABLE_FIELD_ISO_CODE   = 'iso_code';
    public const TABLE_FIELD_LOCALE     = 'locale';
    public const TABLE_FIELD_IS_APP     = 'is_app';
    public const TABLE_FIELD_IS_DEV     = 'is_dev';
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
            self::TABLE_FIELD_ISO_CODE => [
                ['not_empty'],
                ['max_length', [':value', 8]],
            ],
            self::TABLE_FIELD_LOCALE   => [
                ['not_empty'],
                ['max_length', [':value', 8]],
            ],
            self::TABLE_FIELD_IS_APP   => [
                ['not_empty'],
                ['max_length', [':value', 1]],
            ],
            self::TABLE_FIELD_IS_DEV   => [
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
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return string
     */
    public function getLabel(LanguageInterface $lang = null): string
    {
        return (string)$this->getI18nValue($lang ?? $this) ?: (string)$this->getAnyI18nValue();
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
    public function markAsApp(): LanguageInterface
    {
        return $this->markApp(true);
    }

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsNonApp(): LanguageInterface
    {
        return $this->markApp(false);
    }

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsDev(): LanguageInterface
    {
        return $this->markDev(true);
    }

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markAsNonDev(): LanguageInterface
    {
        return $this->markDev(false);
    }

    /**
     * @return bool
     */
    public function isApp(): bool
    {
        return (bool)$this->get(self::TABLE_FIELD_IS_APP);
    }

    /**
     * @return bool
     */
    public function isDev(): bool
    {
        return (bool)$this->get(self::TABLE_FIELD_IS_DEV);
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
    private function markApp(bool $value): LanguageInterface
    {
        $this->set(self::TABLE_FIELD_IS_APP, (int)$value);

        return $this;
    }

    /**
     * @param bool $value
     *
     * @return \BetaKiller\Model\LanguageInterface
     */
    private function markDev(bool $value): LanguageInterface
    {
        $this->set(self::TABLE_FIELD_IS_DEV, (int)$value);

        return $this;
    }
}
