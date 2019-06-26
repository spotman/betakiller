<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\I18nKeyModelInterface;
use BetaKiller\Model\LanguageInterface;

/**
 * Interface I18nKeyRepositoryInterface
 *
 * @package BetaKiller\Repository
 */
interface I18nKeyRepositoryInterface extends DispatchableRepositoryInterface // All keys would be editable via web UI
{
    /**
     * @param string                              $value
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return \BetaKiller\Model\I18nKeyModelInterface|null
     */
    public function findByI18nValue(string $value, LanguageInterface $lang = null): ?I18nKeyModelInterface;

    /**
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return \BetaKiller\Model\I18nKeyModelInterface[]|mixed[]
     */
    public function findKeysWithEmptyValues(LanguageInterface $lang): array;

    /**
     * @return \BetaKiller\Model\I18nKeyModelInterface[]|mixed[]
     */
    public function getAllI18nKeys(): array;

    /**
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return \BetaKiller\Model\I18nKeyModelInterface[]|mixed[]
     */
    public function getAllOrderedByI18nValue(LanguageInterface $lang): array;
}
