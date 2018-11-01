<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class Translation extends AbstractI18nModel
{
    public const TABLE_FIELD_KEY = 'key_id';

    protected function configure(): void
    {
        $this->_table_name = 'i18n_values';

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
