<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class Language extends \ORM implements LanguageInterface
{
    public const TABLE_NAME             = 'languages';
    public const TABLE_FIELD_NAME       = 'name';
    public const TABLE_FIELD_LOCALE     = 'locale';
    public const TABLE_FIELD_LABEL      = 'label';
    public const TABLE_FIELD_IS_SYSTEM  = 'is_system';
    public const TABLE_FIELD_IS_DEFAULT = 'is_default';

    protected function configure(): void
    {
        $this->_table_name = static::TABLE_NAME;
    }

    public function rules(): array
    {
        return [
            self::TABLE_FIELD_NAME      => [
                ['not_empty'],
                ['max_length', [':value', 8]],
            ],
            self::TABLE_FIELD_LOCALE    => [
                ['not_empty'],
                ['max_length', [':value', 8]],
            ],
            self::TABLE_FIELD_LABEL     => [
                ['not_empty'],
                ['max_length', [':value', 16]],
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
    public function setName(string $value): LanguageInterface
    {
        $this->set(self::TABLE_FIELD_NAME, strtolower($value));

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
     * @param string $value
     *
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function setLabel(string $value): LanguageInterface
    {
        $value = trim($value);
        $this->set(self::TABLE_FIELD_LABEL, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->get(self::TABLE_FIELD_LABEL);
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
     * @param bool $value
     *
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function markSystem(bool $value): LanguageInterface
    {
        $this->set(self::TABLE_FIELD_IS_SYSTEM, $value);

        return $this;
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
    public function setIsDefault(bool $value): LanguageInterface
    {
        $this->set(self::TABLE_FIELD_IS_DEFAULT, $value);

        return $this;
    }
}
