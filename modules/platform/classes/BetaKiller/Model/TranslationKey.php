<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class TranslationKey extends \ORM implements I18nKeyModelInterface
{
    /**
     * Custom configuration (set table name, configure relations, load_with(), etc)
     */
    protected function configure(): void
    {
        $this->_table_name = 'i18n_keys';
    }

    /**
     * @return string
     */
    public function getI18nKey(): string
    {
        return $this->get('key');
    }
}
