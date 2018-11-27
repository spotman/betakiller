<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Repository\TranslationKeyRepository;

class DatabaseI18nLoader implements I18nKeysLoaderInterface
{
    /**
     * @var \BetaKiller\Repository\TranslationKeyRepository
     */
    private $repo;

    /**
     * DatabaseI18nLoader constructor.
     *
     * @param \BetaKiller\Repository\TranslationKeyRepository $repo
     */
    public function __construct(TranslationKeyRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Returns keys models with all translations
     *
     * @return \BetaKiller\Model\I18nKeyInterface[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function loadI18nKeys(): array
    {
        // All translation data is already loaded with keys
        return $this->repo->getAll();
    }
}
