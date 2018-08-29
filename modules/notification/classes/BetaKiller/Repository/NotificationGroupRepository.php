<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\NotificationGroupInterface;

class NotificationGroupRepository extends AbstractOrmBasedRepository
{
    /**
     * @param string $groupCodename
     *
     * @return \BetaKiller\Model\ExtendedOrmInterface|mixed
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getGroupUsers(string $groupCodename)
    {
        /*
        SELECT FROM `users` AS `user`
        LEFT JOIN `roles_users`
        ON (`roles_users`.`user_id` = `user`.`id`)
        LEFT JOIN `notification_groups_roles`
        ON (`notification_groups_roles`.`role_id` = `roles_users`.`role_id`)
        LEFT JOIN `notification_groups`
        ON (`notification_groups`.`id` = `notification_groups_roles`.`group_id`)
        LEFT JOIN `notification_groups_users_off`
        ON (`notification_groups_users_off`.`group_id` = `notification_groups_roles`.`group_id` AND `notification_groups_users_off`.`user_id` = `user`.`id`)
        WHERE `notification_groups`.`codename` = '..'
        AND `notification_groups_users_off`.`user_id` IS NULL
        GROUP BY `user`.`id`
         */
        $items = $this->getOrmInstance()
            ->get('group_users')
            ->join('roles_users', 'left')
            ->on('roles_users.user_id', '=', 'user.id')
            ->join('notification_groups_roles', 'left')
            ->on('notification_groups_roles.role_id', '=', 'roles_users.role_id')
            ->join('notification_groups', 'left')
            ->on('notification_groups.id', '=', 'notification_groups_roles.group_id')
            ->join('notification_groups_users_off', 'left')
            ->on('notification_groups_users_off.group_id', '=', 'notification_groups_roles.group_id')
            ->on('notification_groups_users_off.user_id', '=', 'user.id')
            ->where('notification_groups.codename', '=', $groupCodename)
            ->where('notification_groups_users_off.user_id', 'IS', null)
            ->group_by_primary_key()
//            ->having('notification_groups_users_off.user_id', 'IS')
            ->get_all();

        echo $this->getOrmInstance()->last_query();

        return $items;
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
