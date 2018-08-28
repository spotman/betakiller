<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\User;

/**
 * @method TransactionModelInterface[] findAll(OrmInterface $orm)
 */
class NotificationGroupRepository extends AbstractOrmBasedRepository
{
    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        parent::configure();
    }

    /**
     * @return string
     */
    public function getGroupsOff()
    {
        $orm = $this->getOrmInstance();

        return $orm->get_all();
    }

    /**
     * @param int $id
     *
     * @return \BetaKiller\Model\NotificationGroupInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     */
//    public function getItemById(int $id): ?NotificationGroupInterface
//    {
//        return $this->findById((string)$id);
//    }

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
//    public function getItems(): array
//    {
//        $orm = $this->getOrmInstance();
//
//        return $this->findAll($orm);
//    }

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
