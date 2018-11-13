<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class Translation extends AbstractI18nModel implements TranslationModelInterface
{
    public const TABLE_FIELD_KEY = 'key_id';
    public const TABLE_PK = 'id';

    protected function configure(): void
    {
        $this->_table_name = 'i18n_values';

        $this->_primary_key_value = self::TABLE_PK;

        parent::configure();
    }

    /**
     * @return string
     */
    protected function getI18nKeyModelName(): string
    {
        return 'TranslationKey';
    }

    /**
     * @return string
     */
    protected function getI18nKeyForeignKey(): string
    {
        return self::TABLE_FIELD_KEY;
    }
}
