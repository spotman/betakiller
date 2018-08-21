<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\NotificationGroupInterface;

class NotificationGroupRepository extends AbstractOrmBasedRepository
{
    /**
     * @param int $id
     *
     * @return \BetaKiller\Model\NotificationGroupInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getItemById(int $id): ?NotificationGroupInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterById($orm, $id)
            ->findOne($orm);
    }

    /**
     * @param string $codeName
     *
     * @return \BetaKiller\Model\NotificationGroupInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getItemByCodename(string $codeName): ?NotificationGroupInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterByCodename($orm, $codeName)
            ->findOne($orm);
    }

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface[]
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getItems(): array
    {
        $orm = $this->getOrmInstance();

        return $this->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param int                                    $id
     *
     * @return \BetaKiller\Repository\NotificationGroupRepository
     */
    private function filterById(ExtendedOrmInterface $orm, int $id): self
    {
        $orm->where('id', '=', $id);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $codeName
     *
     * @return \BetaKiller\Repository\NotificationGroupRepository
     */
    private function filterByCodename(ExtendedOrmInterface $orm, string $codeName): self
    {
        $orm->where('codename', '=', $codeName);

        return $this;
    }
}
