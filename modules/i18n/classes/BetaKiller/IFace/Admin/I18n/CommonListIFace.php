<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\I18n;

use BetaKiller\I18n\I18nConfigInterface;
use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Repository\TranslationKeyRepositoryInterface;

readonly class CommonListIFace extends AbstractI18nListIFace
{
    /**
     * CommonListIFace constructor.
     *
     * @param \BetaKiller\Repository\TranslationKeyRepositoryInterface $keyRepo
     * @param \BetaKiller\Repository\LanguageRepositoryInterface       $langRepo
     * @param \BetaKiller\I18n\I18nConfigInterface                     $i18nConfig
     */
    public function __construct(
        TranslationKeyRepositoryInterface $keyRepo,
        LanguageRepositoryInterface $langRepo,
        I18nConfigInterface $i18nConfig
    ) {
        parent::__construct($keyRepo, $langRepo, $i18nConfig);
    }
}
