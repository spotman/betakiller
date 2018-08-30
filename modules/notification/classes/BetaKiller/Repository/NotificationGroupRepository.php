<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\NotificationGroup;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\UserInterface;

class NotificationGroupRepository extends AbstractOrmBasedRepository
{
    /**
     * @param \BetaKiller\Model\NotificationGroupInterface $groupModel
     *
     * @return \BetaKiller\Model\UserInterface[]
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function findGroupUsers(NotificationGroupInterface $groupModel): array
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

        $orm = $this
            ->getOrmInstance()
            ->get('users');

        $usersTableAlias = $orm->object_name();

        return $orm
            ->join_related('roles', 'roles')
            ->join(NotificationGroup::ROLES_TABLE_NAME, 'left')
            ->on(
                NotificationGroup::ROLES_TABLE_NAME.'.'.NotificationGroup::ROLES_TABLE_FIELD_ROLE_ID,
                '=',
                'roles.id'
            )
            ->join(NotificationGroup::USERS_OFF_TABLE_NAME, 'left')
            ->on(
                NotificationGroup::USERS_OFF_TABLE_NAME.'.'.NotificationGroup::USERS_OFF_TABLE_FIELD_GROUP_ID,
                '=',
                NotificationGroup::ROLES_TABLE_NAME.'.'.NotificationGroup::ROLES_TABLE_FIELD_GROUP_ID
            )
            ->on(
                NotificationGroup::USERS_OFF_TABLE_NAME.'.'.NotificationGroup::USERS_OFF_TABLE_FIELD_GROUP_ID,
                '=',
                $usersTableAlias.'.id'
            )
            ->join(NotificationGroup::TABLE_NAME, 'left')
            ->on(
                NotificationGroup::TABLE_NAME.'.id',
                '=',
                NotificationGroup::ROLES_TABLE_NAME.'.'.NotificationGroup::ROLES_TABLE_FIELD_GROUP_ID
            )
            ->where(
                NotificationGroup::TABLE_NAME.'.id',
                '=',
                $groupModel
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
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return \BetaKiller\Model\NotificationGroupInterface[]
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getUserGroups(UserInterface $userModel): array
    {
        $orm = $this->getOrmInstance();

        $tableAlias = $orm->object_name();

        return $orm
            ->join(NotificationGroup::ROLES_TABLE_NAME, 'left')
            ->on(
                NotificationGroup::ROLES_TABLE_NAME.'.'.NotificationGroup::ROLES_TABLE_FIELD_GROUP_ID,
                '=',
                $tableAlias.'.id'
            )
            ->where(
                NotificationGroup::ROLES_TABLE_NAME.'.'.NotificationGroup::ROLES_TABLE_FIELD_ROLE_ID,
                'IN',
                $userModel->getAccessControlRoles()
            )
            ->group_by_primary_key()
            ->get_all();
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Model\NotificationGroupInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function findGroup(string $codename): ?NotificationGroupInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterByCodename($orm, $codename)
            ->findOne($orm);
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $codename
     *
     * @return \BetaKiller\Repository\NotificationGroupRepository
     */
    private function filterByCodename(ExtendedOrmInterface $orm, string $codename): self
    {
        $orm->where('codename', '=', $codename);

        return $this;
    }
}
