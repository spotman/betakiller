<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\CountryI18n;
use BetaKiller\Model\CountryI18nInterface;
use BetaKiller\Model\CountryInterface;
use BetaKiller\Model\LanguageInterface;

class CountryI18nRepository extends AbstractOrmBasedRepository implements CountryI18nRepositoryInterface
{
    /**
     * @param \BetaKiller\Model\CountryInterface  $countryModel
     * @param \BetaKiller\Model\LanguageInterface $languageModel
     *
     * @return \BetaKiller\Model\CountryI18nInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function findItem(CountryInterface $countryModel, LanguageInterface $languageModel): ?CountryI18nInterface
    {
        $orm = $this
            ->getOrmInstance()
            ->where(CountryI18n::TABLE_FIELD_COUNTRY_ID, '=', $countryModel)
            ->where(CountryI18n::TABLE_FIELD_LANGUAGE_ID, '=', $languageModel);

        return $this->findOne($orm);
    }
}
