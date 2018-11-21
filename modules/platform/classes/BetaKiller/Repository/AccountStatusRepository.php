<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Exception\DomainException;
use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\AccountStatus;
use BetaKiller\Model\AccountStatusInterface;

/**
 * @method AccountStatusInterface findById(string $id)
 * @method AccountStatusInterface[] getAll()
 */
class AccountStatusRepository extends AbstractOrmBasedRepository implements AccountStatusRepositoryInterface
{
    /**
     * @param string $codeName
     *
     * @return null|\BetaKiller\Model\AccountStatusInterface
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByCodename(string $codeName): ?AccountStatusInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterByCodename($orm, $codeName)
            ->findOne($orm);
    }

    /**
     * @param string $codeName
     *
     * @return \BetaKiller\Model\AccountStatusInterface
     * @throws \BetaKiller\Exception\DomainException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getByCodename(string $codeName): AccountStatusInterface
    {
        $status = $this->findByCodename($codeName);

        if (!$status) {
            throw new DomainException('Unable find account status by codename :value', [
                ':value' => $codeName
            ]);
        }

        return $status;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $codeName
     *
     * @return \BetaKiller\Repository\AccountStatusRepository
     */
    private function filterByCodename(ExtendedOrmInterface $orm, string $codeName): self
    {
        $orm->where(AccountStatus::TABLE_FIELD_CODENAME, '=', $codeName);

        return $this;
    }
}
