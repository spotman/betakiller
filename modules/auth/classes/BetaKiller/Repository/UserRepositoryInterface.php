<?php

declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\Phone;
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
     * @param string $relName
     *
     * @return \Database_Expression
     */
    public static function makeFullNameExpression(string $relName): \Database_Expression;

    /**
     * @param string $relName
     *
     * @return \Database_Expression[]
     */
    public static function makeSearchExpressions(string $relName): array;

    /**
     * Search for user by username or e-mail
     *
     * @param string $loginOrEmail
     *
     * @return \BetaKiller\Model\UserInterface|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function searchBy(string $loginOrEmail): ?UserInterface;

    public function findByEmail(string $email): ?UserInterface;

    public function findByPhone(Phone $phone): ?UserInterface;

    public function findByUsername(string $username): ?UserInterface;

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     *
     * @return UserInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUsersWithRole(RoleInterface $role): array;

    /**
     * @param \BetaKiller\Model\RoleInterface[] $roles
     *
     *
     * @return UserInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUsersWithRoles(array $roles): array;

    /**
     * @param int $page
     *
     * @return UserInterface[]
     */
    public function getBanned(int $page): array;
}
