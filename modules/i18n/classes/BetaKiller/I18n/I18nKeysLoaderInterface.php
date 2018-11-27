<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

interface I18nKeysLoaderInterface
{
    /**
     * Returns keys models with all translations
     *
     * @return \BetaKiller\Model\I18nKeyInterface[]
     */
    public function loadI18nKeys(): array;
}
