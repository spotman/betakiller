<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\I18nKeyInterface;
use BetaKiller\Model\LanguageInterface;

/**
 * Interface I18nKeyRepositoryInterface
 *
 * @package BetaKiller\Repository
 */
interface I18nKeyRepositoryInterface extends DispatchableRepositoryInterface // All keys would be editable via web UI
{
    /**
     * @param array $langModels
     *
     * @return I18nKeyInterface[]|mixed[]
     */
    public function findKeysWithEmptyValues(array $langModels): array;
}
