<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\CityI18nInterface;
use BetaKiller\Model\CityInterface;
use BetaKiller\Model\LanguageInterface;

interface CityI18nRepositoryInterface
{
    /**
     * @param \BetaKiller\Model\CityInterface     $cityModel
     * @param \BetaKiller\Model\LanguageInterface $languageModel
     *
     * @return \BetaKiller\Model\CityI18nInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function findItem(CityInterface $cityModel, LanguageInterface $languageModel): ?CityI18nInterface;
}
