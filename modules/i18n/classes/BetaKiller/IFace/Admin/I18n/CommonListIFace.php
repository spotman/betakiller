<?php
declare(strict_types=1);

namespace BetaKiller\IFace\Admin\I18n;

use BetaKiller\Repository\LanguageRepositoryInterface;
use BetaKiller\Repository\TranslationKeyRepository;

class CommonListIFace extends AbstractI18nListIFace
{
    /**
     * CommonListIFace constructor.
     *
     * @param \BetaKiller\Repository\TranslationKeyRepository    $keyRepo
     * @param \BetaKiller\Repository\LanguageRepositoryInterface $langRepo
     */
    public function __construct(TranslationKeyRepository $keyRepo, LanguageRepositoryInterface $langRepo)
    {
        parent::__construct($keyRepo, $langRepo);
    }
}
