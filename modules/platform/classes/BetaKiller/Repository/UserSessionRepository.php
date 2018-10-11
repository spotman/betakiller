<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Exception\DomainException;
use BetaKiller\Model\GuestUserInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Model\UserSession;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use DateInterval;
use DateTime;

/**
 * Class UserSessionRepository
 *
 * @package BetaKiller\Repository
 * @method UserSession findOne(OrmInterface $orm)
 * @method UserSession[] findAll(OrmInterface $orm)
 * @method UserSession getOrmInstance()
 * @method void delete(UserSession $entity)
 */
class UserSessionRepository extends AbstractOrmBasedRepository
{
    /**
     * Returns null if session is not exists (cleared by gc)
     *
     * @param string $value
     *
     * @return \BetaKiller\Model\UserSession|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByToken(string $value): ?UserSession
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterToken($orm, $value)
            ->findOne($orm);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserSession
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByToken(string $value): UserSession
    {
        $model = $this->findByToken($value);

        if (!$model) {
            throw new RepositoryException('Missing DB record for session :token', [':token' => $value]);
        }

        return $model;
    }

    public function isUserHasSessions(UserInterface $user): bool
    {
        return \count($this->getUserSessions($user)) > 0;
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return UserSession[]
     * @throws \BetaKiller\Exception\DomainException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getUserSessions(UserInterface $user): array
    {
        if ($user instanceof GuestUserInterface) {
            throw new DomainException('Can not check sessions on guest user');
        }

        $orm = $this->getOrmInstance();

        return $this
            ->filterUser($orm, $user)
            ->findAll($orm);
    }

    public function getExpiredSessions(DateInterval $interval): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterExpired($orm, $interval)
            ->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function deleteUserSessions(UserInterface $user): void
    {
        $orm = $this->getOrmInstance();

        $tokens = $this
            ->filterUser($orm, $user)
            ->findAll($orm);

        foreach ($tokens as $token) {
            $this->delete($token);
        }
    }

    private function filterUser(OrmInterface $orm, UserInterface $user): self
    {
        $orm->where($orm->object_column('user_id'), '=', $user->getID());

        return $this;
    }

    private function filterToken(OrmInterface $orm, string $token): self
    {
        $orm->where($orm->object_column('token'), '=', $token);

        return $this;
    }

    private function filterExpired(OrmInterface $orm, DateInterval $interval): self
    {
        return $this->filterLastActive($orm, $interval, true);
    }

    private function filterLastActive(OrmInterface $orm, DateInterval $interval, bool $getExpired): self
    {
        $threshold = (new DateTime())->sub($interval);

        $orm->filter_datetime_column_value('last_active_at', $threshold, $getExpired ? '<' : '>=');

        return $this;
    }
}
