<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\ExtendedOrmInterface;
use BetaKiller\Model\NotificationGroup;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\UserInterface;

class NotificationGroupRepository extends AbstractOrmBasedDispatchableRepository
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return NotificationGroup::TABLE_FIELD_CODENAME;
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Model\NotificationGroupInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function findByCodename(string $codename): ?NotificationGroupInterface
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterByCodename($orm, $codename)
            ->findOne($orm);
    }

    public function getByCodename(string $codename): NotificationGroupInterface
    {
        $group = $this->findByCodename($codename);

        if (!$group) {
            throw new RepositoryException('Group not found by group codename ":codename"', [
                ':codename' => $codename,
            ]);
        }

        return $group;
    }

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface[]
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getAllEnabled(): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterGroupIsEnabled($orm, true)
            ->orderByName($orm)
            ->findAll($orm);
    }

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface[]
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getAllDisabled(): array
    {
        $orm = $this->getOrmInstance();

        return $this
            ->filterGroupIsEnabled($orm, false)
            ->orderByName($orm)
            ->findAll($orm);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @param bool|null                       $includeSystem
     *
     * @return \BetaKiller\Model\NotificationGroupInterface[]
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getUserGroups(UserInterface $user, bool $includeSystem = null): array
    {
        $orm = $this->getOrmInstance();

        if (!$includeSystem) {
            $this->filterSystemGroup($orm, false);
        }

        return $this
            ->filterGroupIsEnabled($orm, true)
            ->filterRoles($orm, $user->getAccessControlRoles())
            ->findAll($orm);
    }

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

        $orm = $this->getOrmInstance()->get('users');

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
                $orm->object_column('id')
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
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param string                                 $codename
     *
     * @return \BetaKiller\Repository\NotificationGroupRepository
     */
    private function filterByCodename(ExtendedOrmInterface $orm, string $codename): self
    {
        $orm->where($orm->object_column('codename'), '=', $codename);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\ExtendedOrmInterface $orm
     * @param bool                                   $value
     *
     * @return \BetaKiller\Repository\NotificationGroupRepository
     */
    private function filterGroupIsEnabled(ExtendedOrmInterface $orm, bool $value): self
    {
        $orm->where(
            $orm->object_column(NotificationGroup::TABLE_FIELD_IS_ENABLED),
            '=',
            $value
        );

        return $this;
    }

    private function filterSystemGroup(ExtendedOrmInterface $orm, bool $value): self
    {
        $orm->where(
            $orm->object_column(NotificationGroup::TABLE_FIELD_IS_SYSTEM),
            '=',
            $value
        );

        return $this;
    }

    private function orderByName(ExtendedOrmInterface $orm): self
    {
        $orm->order_by($orm->object_column(NotificationGroup::TABLE_FIELD_CODENAME), 'asc');

        return $this;
    }

    private function filterRoles(ExtendedOrmInterface $orm, array $roles): self
    {
        $this->filterRelatedMultiple($orm, NotificationGroup::RELATION_ROLES, $roles);

        return $this;
    }
}
