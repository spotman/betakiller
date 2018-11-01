<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\AbstractI18nModel;
use BetaKiller\Model\Translation;

/**
 * Class TranslationRepository
 *
 * @package BetaKiller\Repository
 * @method \BetaKiller\Model\I18nModelInterface create()
 * @method save(\BetaKiller\Model\I18nModelInterface $model)
 */
class TranslationRepository extends AbstractI18nRepository
{
    /**
     * @return string
     */
    protected function getLanguageColumnName(): string
    {
        return AbstractI18nModel::TABLE_FIELD_LANGUAGE_ID;
    }

    /**
     * @return string
     */
    protected function getI18nKeyForeignKey(): string
    {
        return Translation::TABLE_FIELD_KEY;
    }
}
