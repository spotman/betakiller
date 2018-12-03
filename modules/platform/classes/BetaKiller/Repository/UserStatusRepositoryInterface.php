<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\UserStatusInterface;

interface UserStatusRepositoryInterface
{
    /**
     * @param string $codeName
     *
     * @return null|\BetaKiller\Model\UserStatusInterface
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByCodename(string $codeName): ?UserStatusInterface;

    /**
     * @param string $codeName
     *
     * @return \BetaKiller\Model\UserStatusInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByCodename(string $codeName): UserStatusInterface;
}
