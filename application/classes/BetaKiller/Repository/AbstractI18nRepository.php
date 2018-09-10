<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\CityI18n;
use BetaKiller\Model\CityI18nInterface;
use BetaKiller\Model\CityInterface;
use BetaKiller\Model\LanguageInterface;

class CityI18nRepository extends AbstractOrmBasedRepository implements CityI18nRepositoryInterface
{
    /**
     * @param \BetaKiller\Model\CityInterface     $cityModel
     * @param \BetaKiller\Model\LanguageInterface $languageModel
     *
     * @return \BetaKiller\Model\CityI18nInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function findItem(CityInterface $cityModel, LanguageInterface $languageModel): ?CityI18nInterface
    {
        $orm = $this
            ->getOrmInstance()
            ->where(CityI18n::TABLE_FIELD_TARGET_ID, '=', $cityModel)
            ->where(CityI18n::TABLE_FIELD_LANGUAGE_ID, '=', $languageModel);

        return $this->findOne($orm);
    }

    protected function filterByLanguage(ExtendedOrmInterface $orm, LanguageInterface $languageModel): ?CityI18nInterface
    {
        return $orm->where(CityI18n::TABLE_FIELD_TARGET_ID, '=', $cityModel)
    }

    /**
     * @return string
     */
    protected function getLanguageColumnName(): string
    {
        return CityI18n::TABLE_FIELD_TARGET_ID;
    }
}
