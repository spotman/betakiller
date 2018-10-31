<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\I18nKeyModelInterface;
use BetaKiller\Model\I18nModelInterface;
use BetaKiller\Model\LanguageInterface;

interface I18nRepositoryInterface extends RepositoryInterface
{
    /**
     * @param \BetaKiller\Model\LanguageInterface $lang
     *
     * @return \BetaKiller\Model\I18nModelInterface[]
     */
    public function findItemsByLanguage(LanguageInterface $lang): array;

    /**
     * @param \BetaKiller\Model\I18nKeyModelInterface $key
     * @param \BetaKiller\Model\LanguageInterface     $lang
     *
     * @return \BetaKiller\Model\I18nModelInterface|null
     */
    public function findItem(I18nKeyModelInterface $key, LanguageInterface $lang): ?I18nModelInterface;

    /**
     * @param \BetaKiller\Model\I18nKeyModelInterface $key
     *
     * @return \BetaKiller\Model\I18nModelInterface|null
     */
    public function findFirstNoEmpty(I18nKeyModelInterface $key): ?I18nModelInterface;
}
