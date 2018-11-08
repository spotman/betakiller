<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\I18nKeyModelInterface;
use BetaKiller\Model\TranslationKey;
use BetaKiller\Model\TranslationKeyModelInterface;

/**
 * Class TranslationKeyRepository
 *
 * @package BetaKiller\Repository
 * @method TranslationKeyModelInterface create()
 * @method save(TranslationKeyModelInterface $model)
 */
class TranslationKeyRepository extends AbstractOrmBasedDispatchableRepository implements I18nKeyRepositoryInterface
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return TranslationKey::TABLE_FIELD_KEY;
    }

    public function findByKeyName(string $i18nKey): ?I18nKeyModelInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterKey($orm, $i18nKey)
            ->findOne($orm);
    }

    private function filterKey(ExtendedOrmInterface $orm, string $key): self
    {
        $orm->where($orm->object_column(TranslationKey::TABLE_FIELD_KEY), '=', $key);

        return $this;
    }
}
