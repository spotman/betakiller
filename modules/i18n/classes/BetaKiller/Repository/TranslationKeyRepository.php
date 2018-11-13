<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\TranslationKey;
use BetaKiller\Model\TranslationKeyModelInterface;

/**
 * Class TranslationKeyRepository
 *
 * @package BetaKiller\Repository
 * @method TranslationKeyModelInterface create()
 * @method save(TranslationKeyModelInterface $model)
 */
class TranslationKeyRepository extends AbstractI18nKeyRepository
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return TranslationKey::TABLE_FIELD_KEY;
    }

    protected function getKeyFieldName(): string
    {
        return TranslationKey::TABLE_FIELD_KEY;
    }
}
