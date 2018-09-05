<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\CountryI18nInterface;
use BetaKiller\Model\CountryInterface;
use BetaKiller\Model\LanguageInterface;

interface CountryI18nRepositoryInterface
{
    /**
     * @param \BetaKiller\Model\CountryInterface  $countryModel
     * @param \BetaKiller\Model\LanguageInterface $languageModel
     *
     * @return \BetaKiller\Model\CountryI18nInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function findItem(CountryInterface $countryModel, LanguageInterface $languageModel): ?CountryI18nInterface;
}
