<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\AccountStatusInterface;

interface AccountStatusRepositoryInterface
{
    /**
     * @param string $codeName
     *
     * @return null|\BetaKiller\Model\AccountStatusInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByCodename(string $codeName): ?AccountStatusInterface;
}
