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
        SELECT g.codename,u.id,goff.user_id
        FROM `notification_groups` AS `g`
        JOIN `users` AS `u`
        LEFT JOIN `roles_users` AS `ru`
        ON ru.user_id=u.id
        right JOIN `notification_groups_roles` AS `gr`
        ON `gr`.role_id=ru.role_id
        LEFT JOIN `notification_groups_users_off` AS `goff`
        ON goff.user_id=u.id
        WHERE g.codename="test3"
        GROUP BY u.`id`
        HAVING goff.user_id IS NULL
        */
        /*
        SELECT `notificationgroup`.`id` AS `id`, `notificationgroup`.`codename` AS `codename`, `notificationgroup`.`description` AS `description`
        FROM `notification_groups` AS `notificationgroup`
        JOIN `users` ON ()
        LEFT JOIN `roles_users`
        ON (`roles_users`.`user_id` = `users`.`id`)
        RIGHT JOIN `notification_groups_roles`
        ON (`notification_groups_roles`.`role_id` = `roles_users`.`role_id`)
        LEFT JOIN `notification_groups_users_off`
        ON (`notification_groups_users_off`.`user_id` = `users`.`id`)
        WHERE `notification_groups`.`codename` = 'groupCodename1'
        GROUP BY `users`.`id`
        HAVING `notification_groups_users_off`.`users` IS NULL
         */
        $items = $this->getOrmInstance()
            ->join('users')
            ->on('users.id', '>', 0)
            ->join('roles_users', 'left')
            ->on('roles_users.user_id', '=', 'users.id')
            ->join('notification_groups_roles', 'left')
            ->on('notification_groups_roles.role_id', '=', 'roles_users.role_id')
            ->join('notification_groups_users_off', 'left')
            ->on('notification_groups_users_off.user_id', '=', 'users.id')
//            ->where('notification_groups.codename', '=', $groupCodename)
            ->group_by('users.id')
            ->having('notification_groups_users_off.users', 'IS')
            ->get_all();


        /*$items = $this->getOrmInstance()
            ->get('group_users')
//            ->join('users')
//            ->on('users.id', '>', 0)
            ->join('roles_users', 'left')
            ->on('roles_users.user_id', '=', 'user.id')
            ->join('notification_groups_roles', 'left')
            ->on('notification_groups_roles.role_id', '=', 'roles_users.role_id')
//            ->join('notification_groups_users_off', 'left')
//            ->on('notification_groups_users_off.user_id', '=', 'user.id')
//            ->where('notification_groups.codename', '=', $groupCodename)
            ->group_by_primary_key()
//            ->having('notification_groups_users_off.users', 'IS')
            ->get_all();*/

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
