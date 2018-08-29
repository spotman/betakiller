<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\NotificationGroup;
use BetaKiller\Model\NotificationGroupUserOff;

class NotificationGroupUserOffRepository extends AbstractOrmBasedRepository
{
    /**
     * @param string $groupCodename
     * @param int    $userId
     *
     * @return \BetaKiller\Model\NotificationGroupUserOff
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function get(string $groupCodename, int $userId): NotificationGroupUserOff
    {
        $orm        = $this->getOrmInstance();
        $tableAlias = $orm->object_name();

        return $orm
            ->join_related('groups', 'groups')
            ->where(
                $tableAlias.'.'.NotificationGroupUserOff::TABLE_FIELD_USER_ID,
                '=',
                $userId
            )
            ->where(
                'groups.'.NotificationGroup::TABLE_FIELD_CODENAME,
                '=',
                $groupCodename
            )
            ->find();
    }
}
