<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Repository\LanguageRepository;
use BetaKiller\Repository\TranslationRepository;

class DatabaseTranslationLoader extends AbstractI18nRepositoryLoader
{
    /**
     * DatabaseTranslationLoader constructor.
     *
     * @param \BetaKiller\Repository\LanguageRepository    $langRepo
     * @param \BetaKiller\Repository\TranslationRepository $i18nRepo
     */
    public function __construct(LanguageRepository $langRepo, TranslationRepository $i18nRepo)
    {
        parent::__construct($langRepo, $i18nRepo);
    }
}
