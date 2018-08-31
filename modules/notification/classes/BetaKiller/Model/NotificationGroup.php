<?php
declare(strict_types=1);

namespace BetaKiller\Model;

class NotificationGroup extends \ORM implements NotificationGroupInterface
{
    public const TABLE_NAME              = 'notification_groups';
    public const TABLE_FIELD_IS_ENABLED  = 'is_enabled';
    public const TABLE_FIELD_CODENAME    = 'codename';
    public const TABLE_FIELD_DESCRIPTION = 'description';

    public const ROLES_TABLE_NAME           = 'notification_groups_roles';
    public const ROLES_TABLE_FIELD_GROUP_ID = 'group_id';
    public const ROLES_TABLE_FIELD_ROLE_ID  = 'role_id';

    public const USERS_OFF_TABLE_NAME           = 'notification_groups_users_off';
    public const USERS_OFF_TABLE_FIELD_GROUP_ID = 'group_id';
    public const USERS_OFF_TABLE_FIELD_USER_ID  = 'user_id';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->belongs_to([
            'users' => [
                'model'       => 'User',
                'foreign_key' => 'id',
            ],
        ]);

        $this->has_many([
            'users_off' => [
                'model'       => 'User',
                'through'     => self::USERS_OFF_TABLE_NAME,
                'foreign_key' => self::USERS_OFF_TABLE_FIELD_GROUP_ID,
                'far_key'     => self::USERS_OFF_TABLE_FIELD_USER_ID,
            ],
            'roles'     => [
                'model'       => 'Role',
                'through'     => self::ROLES_TABLE_NAME,
                'foreign_key' => self::ROLES_TABLE_FIELD_GROUP_ID,
                'far_key'     => self::ROLES_TABLE_FIELD_ROLE_ID,
            ],
        ]);

        parent::configure();
    }

    public function rules(): array
    {
        return [
            self::TABLE_FIELD_CODENAME    => [
                ['not_empty'],
                ['min_length', [':value', 4]],
                ['max_length', [':value', 32]],
            ],
            self::TABLE_FIELD_DESCRIPTION => [
                ['max_length', [':value', 255]],
            ],
        ];
    }

    /**
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        return (bool)$this->get(self::TABLE_FIELD_IS_ENABLED);
    }

    /**
     * @param bool $state
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function setIsEnabled($state): NotificationGroupInterface
    {
        $this->set(self::TABLE_FIELD_IS_ENABLED, (bool)$state);

        return $this;
    }

    /**
     * @return string
     */
    public function getCodename(): string
    {
        return $this->get(self::TABLE_FIELD_CODENAME);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function setCodename(string $value): NotificationGroupInterface
    {
        $value = trim($value);
        $this->set(self::TABLE_FIELD_CODENAME, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string)$this->get(self::TABLE_FIELD_DESCRIPTION);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function setDescription(string $value): NotificationGroupInterface
    {
        $value = trim($value);
        $this->set(self::TABLE_FIELD_DESCRIPTION, $value);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return bool
     */
    public function isEnabledForUser(UserInterface $userModel): bool
    {
        return !$this->has('users_off', $userModel);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function enableForUser(UserInterface $userModel): NotificationGroupInterface
    {
        $this->remove('users_off', $userModel);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function disableForUser(UserInterface $userModel): NotificationGroupInterface
    {
        $this->add('users_off', $userModel);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\RoleInterface $roleModel
     *
     * @return bool
     */
    public function isEnabledForRole(RoleInterface $roleModel): bool
    {
        return $this->has('roles', $roleModel);
    }

    /**
     * @param \BetaKiller\Model\RoleInterface $roleModel
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function enableForRole(RoleInterface $roleModel): NotificationGroupInterface
    {
        $this->add('roles', $roleModel);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\RoleInterface $roleModel
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function disableForRole(RoleInterface $roleModel): NotificationGroupInterface
    {
        $this->remove('roles', $roleModel);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\RoleInterface[]
     */
    public function findRoles(): array
    {
        return $this->get('roles')->get_all();
    }
}
