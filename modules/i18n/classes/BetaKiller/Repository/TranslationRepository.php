<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\AbstractI18nModel;
use BetaKiller\Model\Translation;

/**
 * Class TranslationRepository
 *
 * @package BetaKiller\Repository
 * @method \BetaKiller\Model\TranslationModelInterface create()
 * @method save(\BetaKiller\Model\I18nModelInterface $model)
 */
class TranslationRepository extends AbstractI18nRepository
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return Translation::TABLE_PK;
    }

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

    /**
     * @return string
     */
    protected function getI18nKeyRelationName(): string
    {
        return Translation::RELATION_KEY;
    }
}
