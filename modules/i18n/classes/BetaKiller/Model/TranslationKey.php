<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\I18n\I18nFacade;

class TranslationKey extends \ORM implements TranslationKeyModelInterface
{
    use I18nKeyOrmTrait;

    public const COL_CODENAME  = 'codename';
    public const COL_I18N      = 'i18n';
    public const COL_IS_PLURAL = 'is_plural';

    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_table_name = 'i18n_keys';
    }

    protected function getI18nValueColumn(): string
    {
        return self::COL_I18N;
    }

    /**
     * Rule definitions for validation
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            self::COL_CODENAME => [
                ['not_empty'],
                ['max_length', [':value', 128]],
                ['regex', [':value', I18nFacade::KEY_REGEX]],
            ],
        ];
    }

    /**
     * @return string
     */
    public function getI18nKeyName(): string
    {
        return $this->get(self::COL_CODENAME);
    }

    /**
     * @param string $keyName
     */
    public function setI18nKey(string $keyName): void
    {
        $this->set(self::COL_CODENAME, $keyName);
    }

    /**
     * @return bool
     */
    public function isPlural(): bool
    {
        return (bool)$this->get(self::COL_IS_PLURAL);
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
        $this->set(self::COL_IS_PLURAL, (int)$value);
    }
}
