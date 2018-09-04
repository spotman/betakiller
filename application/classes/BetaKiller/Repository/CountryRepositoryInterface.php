<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\CountryInterface;

interface CountryRepositoryInterface
{
    /**
     * @param string $isoCode
     *
     * @return \BetaKiller\Model\CountryInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByIsoCode(string $isoCode): ?CountryInterface;
}
