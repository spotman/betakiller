<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\TranslationKeyModelInterface;

/**
 * Class TranslationKeyRepositoryInterface
 *
 * @package BetaKiller\Repository
 * @method save(TranslationKeyModelInterface $model)
 * @method TranslationKeyModelInterface[] getAll()
 */
interface TranslationKeyRepositoryInterface extends DispatchableRepositoryInterface
{
    public function findByKeyName(string $i18nKey): ?TranslationKeyModelInterface;
}
