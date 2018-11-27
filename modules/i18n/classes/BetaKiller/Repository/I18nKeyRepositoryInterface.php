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
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return \BetaKiller\Model\I18nKeyModelInterface[]|mixed[]
     */
    public function findKeysWithEmptyValues(LanguageInterface $lang): array;
}
