<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\UserInterface;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Search for user by username or e-mail
     *
     * @param string $loginOrEmail
     *
     * @return \BetaKiller\Model\UserInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function searchBy(string $loginOrEmail): ?UserInterface;

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return UserInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUsersWithRole(RoleInterface $role): array;
}