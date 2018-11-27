<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class TranslationKey extends \ORM implements TranslationKeyModelInterface
{
    use I18nKeyOrmTrait;

    public const TABLE_FIELD_KEY       = 'key';
    public const TABLE_FIELD_I18N      = 'i18n';
    public const TABLE_FIELD_IS_PLURAL = 'is_plural';

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_table_name = 'i18n_keys';
    }

    protected function getI18nValueColumn(): string
    {
        return self::TABLE_FIELD_I18N;
    }

    /**
     * @return string
     */
    public function getI18nKeyName(): string
    {
        return $this->get(self::TABLE_FIELD_KEY);
    }

    /**
     * @param string $keyName
     */
    public function setI18nKey(string $keyName): void
    {
        $this->set(self::TABLE_FIELD_KEY, $keyName);
    }

    /**
     * @return bool
     */
    public function isPlural(): bool
    {
        return (bool)$this->get(self::TABLE_FIELD_IS_PLURAL);
    }

    public function markAsPlural(): void
    {
        $this->setPlural(true);
    }

    public function markAsRegular(): void
    {
        $this->setPlural(false);
    }

    private function setPlural(bool $value): void
    {
        $this->set(self::TABLE_FIELD_IS_PLURAL, $value);
    }
}
