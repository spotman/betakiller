<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\NotificationGroup;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationGroupRole;
use BetaKiller\Model\NotificationGroupUserOff;

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
        $orm             = $this->getOrmInstance()->get('users');
        $usersTableAlias = $orm->object_name();

        return $orm
            ->join_related('roles', 'roles')
            ->join(NotificationGroupRole::TABLE_NAME, 'left')
            ->on(
                NotificationGroupRole::TABLE_NAME.'.'.NotificationGroupRole::TABLE_FIELD_ROLE_ID,
                '=',
                'roles.id'
            )
            ->join(NotificationGroupUserOff::TABLE_NAME, 'left')
            ->on(
                NotificationGroupUserOff::TABLE_NAME.'.'.NotificationGroupUserOff::TABLE_FIELD_GROUP_ID,
                '=',
                NotificationGroupRole::TABLE_NAME.'.'.NotificationGroupRole::TABLE_FIELD_GROUP_ID
            )
            ->on(
                NotificationGroupUserOff::TABLE_NAME.'.'.NotificationGroupUserOff::TABLE_FIELD_GROUP_ID,
                '=',
                $usersTableAlias.'.id'
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
                NotificationGroupUserOff::TABLE_NAME.'.'.NotificationGroupUserOff::TABLE_FIELD_USER_ID,
                'IS',
                null
            )
            ->group_by_primary_key()
            ->get_all();
    }

    /**
     * @param array $rolesIds
     *
     * @return array
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getRolesGroups(array $rolesIds): array
    {
        return $this
            ->getOrmInstance()
            ->join_related('roles', 'roles')
            ->where(
                'roles'.'.'.NotificationGroupRole::TABLE_FIELD_ROLE_ID,
                'IN',
                $rolesIds
            )
            ->group_by_primary_key()
            ->get_all();
    }

    /**
     * @param array $rolesIds
     * @param int   $userId
     *
     * @return array
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getRolesGroupsActive(array $rolesIds, int $userId): array
    {
        return $this
            ->getOrmInstance()
            ->join_related('roles', 'roles')
            ->join(NotificationGroupUserOff::TABLE_NAME, 'left')
            ->on(
                NotificationGroupUserOff::TABLE_NAME.'.'.NotificationGroupUserOff::TABLE_FIELD_GROUP_ID,
                '=',
                'roles'.'.'.NotificationGroupRole::TABLE_FIELD_GROUP_ID
            )
            ->on(
                NotificationGroupUserOff::TABLE_NAME.'.'.NotificationGroupUserOff::TABLE_FIELD_USER_ID,
                '=',
                \DB::expr($userId)
            )
            ->where(
                'roles'.'.'.NotificationGroupRole::TABLE_FIELD_ROLE_ID,
                'IN',
                $rolesIds
            )
            ->where(
                NotificationGroupUserOff::TABLE_NAME.'.'.NotificationGroupUserOff::TABLE_FIELD_USER_ID,
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
