<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\NotificationGroup;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationGroupRole;

class NotificationGroupRepository extends AbstractOrmBasedRepository
{
    /**
     * @param string $groupCodename
     *
     * @return \BetaKiller\Model\UserInterface[]
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getGroupUsers(string $groupCodename): array
    {
        /*
        SELECT `user`.*
        FROM `users` AS `user`
        LEFT JOIN `roles_users` AS `roles:through`
        ON (`roles:through`.`user_id` = `user`.`id`)
        LEFT JOIN `roles` AS `roles`
        ON (`roles`.`id` = `roles:through`.`role_id`)
        LEFT JOIN `notification_groups_roles`
        ON (`notification_groups_roles`.`role_id` = `roles`.`id`)
        LEFT JOIN `notification_groups_users_off`
        ON (`notification_groups_users_off`.`group_id` = `notification_groups_roles`.`group_id` AND `notification_groups_users_off`.`user_id` = `user`.`id`)
        LEFT JOIN `notification_groups`
        ON (`notification_groups`.`id` = `notification_groups_roles`.`group_id`)
        WHERE `notification_groups`.`codename` = 'groupCodename2'
        AND `notification_groups_users_off`.`user_id` IS NULL
        GROUP BY `user`.`id`
         */
        return $this->getOrmInstance()
            ->get('users')
            ->join_related('roles', 'roles')
            ->join(NotificationGroupRole::TABLE_NAME, 'left')
            ->on(
                NotificationGroupRole::TABLE_NAME.'.'.NotificationGroupRole::TABLE_FIELD_ROLE_ID,
                '=',
                'roles.id'
            )
            ->join(NotificationGroup::USERS_OFF_TABLE_NAME, 'left')
            ->on(
                NotificationGroup::USERS_OFF_TABLE_NAME.'.'.NotificationGroup::USERS_OFF_TABLE_FIELD_GROUP_ID,
                '=',
                NotificationGroupRole::TABLE_NAME.'.'.NotificationGroupRole::TABLE_FIELD_GROUP_ID
            )
            ->on(
                NotificationGroup::USERS_OFF_TABLE_NAME.'.'.NotificationGroup::USERS_OFF_TABLE_FIELD_GROUP_ID,
                '=',
                'user.id'
            )
            ->join(NotificationGroup::TABLE_NAME, 'left')
            ->on(
                NotificationGroup::TABLE_NAME.'.id',
                '=',
                NotificationGroupRole::TABLE_NAME.'.'.NotificationGroupRole::TABLE_FIELD_GROUP_ID
            )
            ->where(
                NotificationGroup::TABLE_NAME.'.'.NotificationGroup::TABLE_FIELD_CODENAME,
                '=',
                $groupCodename
            )
            ->where(
                NotificationGroup::USERS_OFF_TABLE_NAME.'.'.NotificationGroup::USERS_OFF_TABLE_FIELD_USER_ID,
                'IS',
                null
            )
            ->group_by_primary_key()
            ->get_all();
    }

    /**
     * @param string $codeName
     *
     * @return \BetaKiller\Model\NotificationGroupInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getByCodename(string $codeName): ?NotificationGroupInterface
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
