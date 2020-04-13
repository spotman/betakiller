<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Repository\TranslationKeyRepositoryInterface;

class DatabaseI18nLoader implements I18nKeysLoaderInterface
{
    /**
     * @var \BetaKiller\Repository\TranslationKeyRepositoryInterface
     */
    private $repo;

    /**
     * DatabaseI18nLoader constructor.
     *
     * @param \BetaKiller\Repository\TranslationKeyRepositoryInterface $repo
     */
    public function __construct(TranslationKeyRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Returns keys models with all translations
     *
     * @return \BetaKiller\Model\I18nKeyInterface[]
     */
    public function loadI18nKeys(): array
    {
        // All translation data is already loaded with keys
        return $this->repo->getAll();
    }
}
