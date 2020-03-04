<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\UserInterface;

/**
 * Interface UserRepositoryInterface
 *
 * @package BetaKiller\Repository
 * @method UserInterface getById(string $id)
 */
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
     * @param bool|null                       $checkNested
     *
     * @return UserInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUsersWithRole(RoleInterface $role, bool $checkNested = null): array;

    /**
     * @param \BetaKiller\Model\RoleInterface[] $roles
     *
     * @param bool|null                         $checkNested
     *
     * @return UserInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUsersWithRoles(array $roles, bool $checkNested = null): array;
}
