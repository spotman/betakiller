<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Exception\DomainException;
use BetaKiller\Model\GuestUserInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Model\UserSessionInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use DateInterval;

/**
 * Class UserSessionRepository
 *
 * @package BetaKiller\Repository
 * @method UserSessionInterface findOne(OrmInterface $orm)
 * @method UserSessionInterface[] findAll(OrmInterface $orm)
 * @method void delete(UserSessionInterface $entity)
 */
interface UserSessionRepositoryInterface extends RepositoryInterface
{
    /**
     * Returns null if session is not exists (cleared by gc)
     *
     * @param string $value
     *
     * @return \BetaKiller\Model\UserSession|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByToken(string $value): ?UserSessionInterface;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserSessionInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByToken(string $value): UserSessionInterface;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     */
    public function isUserHasSessions(UserInterface $user): bool;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return UserSessionInterface[]
     * @throws \BetaKiller\Exception\DomainException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUserSessions(UserInterface $user): array;

    /**
     * @param \DateInterval $interval
     *
     * @return array
     */
    public function getExpiredSessions(DateInterval $interval): array;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function deleteUserSessions(UserInterface $user): void;
}
