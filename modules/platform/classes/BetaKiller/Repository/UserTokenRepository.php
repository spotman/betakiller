<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\UserInterface;
use BetaKiller\Model\UserToken;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Class UserTokenRepository
 *
 * @package BetaKiller\Repository
 * @method UserToken findOne(OrmInterface $orm)
 * @method UserToken[] findAll(OrmInterface $orm)
 * @method UserToken getOrmInstance()
 */
class UserTokenRepository extends AbstractOrmBasedRepository
{
    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserToken|null
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByToken(string $value): ?UserToken
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterToken($orm, $value)
            ->findOne($orm);
    }

    /**
     * Handles garbage collection and deleting of expired objects.
     *
     * @return bool
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function collectGarbage(): bool
    {
        if (random_int(1, 100) === 1) {
            // Do garbage collection
            $this->deleteExpired();

            return true;
        }

        return false;
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function deleteUserTokens(UserInterface $user): void
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

    private function filterExpired(OrmInterface $orm): self
    {
        $orm->where($orm->object_column('expires'), '<', time());

        return $this;
    }

    /**
     * Deletes all expired tokens.
     *
     * @return  void
     */
    private function deleteExpired(): void
    {
        $orm = $this->getOrmInstance();

        foreach ($this->filterExpired($orm)->findAll($orm) as $token) {
            $this->delete($token);
        }
    }
}
