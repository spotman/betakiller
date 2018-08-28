<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\GroupInterface;

class GroupRepository extends AbstractOrmBasedRepository
{
    /**
     * @return \BetaKiller\Model\GroupInterface[]
     */
    public function getGroupsOff(): array
    {
        $orm = $this->getOrmInstance();

        $user = \ORM::factory('User');
        echo $user->get_id();
        $orm->join_related('notification_groups_users_off', 'users');
        $orm->where(
            'users.id', '=', '??'
        );

        return $orm->get_all();
    }

    /**
     * @param int $id
     *
     * @return \BetaKiller\Model\GroupInterface|null
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
     * @return \BetaKiller\Model\GroupInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getItemByCodename(string $codeName): ?GroupInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterByCodename($orm, $codeName)
            ->findOne($orm);
    }

    /**
     * @return \BetaKiller\Model\GroupInterface[]
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
     * @return \BetaKiller\Repository\GroupRepository
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
     * @return \BetaKiller\Repository\GroupRepository
     */
    private function filterByCodename(ExtendedOrmInterface $orm, string $codeName): self
    {
        $orm->where('codename', '=', $codeName);

        return $this;
    }
}
