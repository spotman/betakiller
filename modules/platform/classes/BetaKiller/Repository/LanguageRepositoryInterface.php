<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\LanguageInterface;

interface LanguageRepositoryInterface extends RepositoryInterface
{
    /**
     * @param string $name
     *
     * @return \BetaKiller\Model\LanguageInterface|null
     */
    public function findByName(string $name): ?LanguageInterface;

    /**
     * @return \BetaKiller\Model\Language[]
     */
    public function getAllSystem(): array;
}
