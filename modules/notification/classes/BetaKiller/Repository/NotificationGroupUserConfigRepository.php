<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationGroupUserConfig;
use BetaKiller\Model\NotificationGroupUserConfigInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

class NotificationGroupUserConfigRepository extends AbstractOrmBasedRepository implements
    NotificationGroupUserConfigRepositoryInterface
{
    /**
     *
     * @param \BetaKiller\Model\NotificationGroupInterface $group
     *
     * @param \BetaKiller\Model\UserInterface              $user
     *
     * @return \BetaKiller\Model\NotificationGroupUserConfigInterface|null
     */
    public function findByUserAndGroup(
        NotificationGroupInterface $group,
        UserInterface $user
    ): ?NotificationGroupUserConfigInterface {
        $orm = $this->getOrmInstance();

        return $this
            ->filterUser($orm, $user)
            ->filterGroup($orm, $group)
            ->findOne($orm);
    }

    private function filterUser(OrmInterface $orm, UserInterface $user): self
    {
        $orm->where($orm->object_column(NotificationGroupUserConfig::COL_USER_ID), '=', $user->getID());

        return $this;
    }

    private function filterGroup(OrmInterface $orm, NotificationGroupInterface $group): self
    {
        $orm->where($orm->object_column(NotificationGroupUserConfig::COL_GROUP_ID), '=', $group->getID());

        return $this;
    }
}
