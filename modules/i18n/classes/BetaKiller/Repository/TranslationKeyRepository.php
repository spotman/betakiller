<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\TranslationKey;
use BetaKiller\Model\TranslationKeyModelInterface;

/**
 * Class TranslationKeyRepository
 *
 * @package BetaKiller\Repository
 * @method save(TranslationKeyModelInterface $model)
 * @method TranslationKeyModelInterface[] getAll()
 */
class TranslationKeyRepository extends AbstractI18nKeyRepository implements TranslationKeyRepositoryInterface
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return TranslationKey::COL_CODENAME;
    }

    public function findByKeyName(string $i18nKey): ?TranslationKeyModelInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterKey($orm, $i18nKey)
            ->findOne($orm);
    }

    private function filterKey(ExtendedOrmInterface $orm, string $key): self
    {
        $orm->where($orm->object_column(TranslationKey::COL_CODENAME), '=', $key);

        return $this;
    }

    protected function getI18nValuesColumnName(ExtendedOrmInterface $orm): string
    {
        return $orm->object_column(TranslationKey::COL_I18N);
    }
}
