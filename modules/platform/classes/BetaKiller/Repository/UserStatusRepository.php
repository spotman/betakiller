<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Exception\DomainException;
use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\UserStatus;
use BetaKiller\Model\UserStatusInterface;

/**
 * @method UserStatusInterface findById(string $id)
 * @method UserStatusInterface[] getAll()
 */
class UserStatusRepository extends AbstractOrmBasedRepository implements UserStatusRepositoryInterface
{
    /**
     * @param string $codeName
     *
     * @return null|\BetaKiller\Model\UserStatusInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByCodename(string $codeName): ?UserStatusInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterByCodename($orm, $codeName)
            ->findOne($orm);
    }

    /**
     * @param string $codeName
     *
     * @return \BetaKiller\Model\UserStatusInterface
     * @throws \BetaKiller\Exception\DomainException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByCodename(string $codeName): UserStatusInterface
    {
        $status = $this->findByCodename($codeName);

        if (!$status) {
            throw new DomainException('Unable find account status by codename :value', [
                ':value' => $codeName,
            ]);
        }

        return $status;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $codeName
     *
     * @return \BetaKiller\Repository\UserStatusRepository
     */
    private function filterByCodename(ExtendedOrmInterface $orm, string $codeName): self
    {
        $orm->where(UserStatus::TABLE_FIELD_CODENAME, '=', $codeName);

        return $this;
    }
}
