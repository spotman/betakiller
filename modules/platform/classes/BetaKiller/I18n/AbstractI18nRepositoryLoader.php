<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Repository\I18nRepositoryInterface;
use BetaKiller\Repository\LanguageRepository;

abstract class AbstractI18nRepositoryLoader implements LoaderInterface
{
    /**
     * @var \BetaKiller\Repository\LanguageRepository
     */
    private $langRepo;

    /**
     * @var \BetaKiller\Repository\I18nRepositoryInterface
     */
    private $i18nRepo;

    /**
     * AbstractI18nRepositoryLoader constructor.
     *
     * @param \BetaKiller\Repository\LanguageRepository      $langRepo
     * @param \BetaKiller\Repository\I18nRepositoryInterface $i18nRepo
     */
    public function __construct(LanguageRepository $langRepo, I18nRepositoryInterface $i18nRepo)
    {
        $this->langRepo = $langRepo;
        $this->i18nRepo = $i18nRepo;
    }

    /**
     * Returns "key" => "translated string" pairs for provided locale
     *
     * @param string $locale
     *
     * @return string[]
     */
    public function load(string $locale): array
    {
        $language = $this->langRepo->getByLocale($locale);

        $data = [];

        foreach ($this->i18nRepo->findItemsByLanguage($language) as $model) {
            $data[$model->getKey()->getI18nKey()] = $model->getValue();
        }

        return $data;
    }
}
