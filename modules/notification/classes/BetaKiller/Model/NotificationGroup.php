<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use ORM;

class NotificationGroup extends ORM implements NotificationGroupInterface
{
    public const TABLE_NAME             = 'notification_groups';
    public const COL_IS_ENABLED         = 'is_enabled';
    public const COL_CODENAME           = 'codename';
    public const COL_DESCRIPTION        = 'description';
    public const COL_IS_SYSTEM          = 'is_system';
    public const COL_IS_FREQ_CONTROLLED = 'is_freq_controlled';
    public const COL_PLACE              = 'place';

    public const ROLES_TABLE_NAME           = 'notification_groups_roles';
    public const ROLES_TABLE_FIELD_GROUP_ID = 'group_id';
    public const ROLES_TABLE_FIELD_ROLE_ID  = 'role_id';

    public const USERS_OFF_TABLE_NAME           = 'notification_groups_users_off';
    public const USERS_OFF_TABLE_FIELD_GROUP_ID = 'group_id';
    public const USERS_OFF_TABLE_FIELD_USER_ID  = 'user_id';

    public const RELATION_ROLES     = 'roles';
    public const RELATION_USERS_OFF = 'users_off';

    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        // TODO Понять почему без этого не работает NotificationGroupRepository::findGroupUsers()
        $this->belongs_to([
            'users' => [
                'model'       => 'User',
                'foreign_key' => 'id',
            ],
        ]);

        $this->has_many([
            self::RELATION_USERS_OFF => [
                'model'       => 'User',
                'through'     => self::USERS_OFF_TABLE_NAME,
                'foreign_key' => self::USERS_OFF_TABLE_FIELD_GROUP_ID,
                'far_key'     => self::USERS_OFF_TABLE_FIELD_USER_ID,
            ],
            self::RELATION_ROLES     => [
                'model'       => 'Role',
                'through'     => self::ROLES_TABLE_NAME,
                'foreign_key' => self::ROLES_TABLE_FIELD_GROUP_ID,
                'far_key'     => self::ROLES_TABLE_FIELD_ROLE_ID,
            ],
        ]);
    }

    public function rules(): array
    {
        return [
            self::COL_CODENAME => [
                ['not_empty'],
                ['min_length', [':value', 4]],
                ['max_length', [':value', 32]],
            ],

            self::COL_DESCRIPTION => [
                ['max_length', [':value', 255]],
            ],
        ];
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool)$this->get(self::COL_IS_ENABLED);
    }

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function enable(): NotificationGroupInterface
    {
        $this->set(self::COL_IS_ENABLED, true);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function disable(): NotificationGroupInterface
    {
        $this->set(self::COL_IS_ENABLED, false);

        return $this;
    }

    /**
     * @return string
     */
    public function getCodename(): string
    {
        return $this->get(self::COL_CODENAME);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function setCodename(string $value): NotificationGroupInterface
    {
        $value = trim($value);
        $this->set(self::COL_CODENAME, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return (string)$this->get(self::COL_DESCRIPTION);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function setDescription(string $value): NotificationGroupInterface
    {
        $value = trim($value);
        $this->set(self::COL_DESCRIPTION, $value);

        return $this;
    }

    /**
     * @return bool
     */
    public function isFrequencyControlEnabled(): bool
    {
        return (bool)$this->get(self::COL_IS_FREQ_CONTROLLED);
    }

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function enableFrequencyControl(): NotificationGroupInterface
    {
        $this->set(self::COL_IS_FREQ_CONTROLLED, true);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function disableFrequencyControl(): NotificationGroupInterface
    {
        $this->set(self::COL_IS_FREQ_CONTROLLED, false);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     */
    public function isEnabledForUser(UserInterface $user): bool
    {
        return !$this->has(self::RELATION_USERS_OFF, $user);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     */
    public function isAllowedToUser(UserInterface $user): bool
    {
        // User has one of group roles => allowed
        foreach ($this->getRoles() as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        // No roles intersection => not allowed
        return false;
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function enableForUser(UserInterface $user): NotificationGroupInterface
    {
        $this->remove(self::RELATION_USERS_OFF, $user);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function disableForUser(UserInterface $user): NotificationGroupInterface
    {
        $this->add(self::RELATION_USERS_OFF, $user);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return bool
     */
    public function hasRole(RoleInterface $role): bool
    {
        return $this->has(self::RELATION_ROLES, $role);
    }

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function addRole(RoleInterface $role): NotificationGroupInterface
    {
        $this->add(self::RELATION_ROLES, $role);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function removeRole(RoleInterface $role): NotificationGroupInterface
    {
        $this->remove(self::RELATION_ROLES, $role);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\RoleInterface[]
     */
    public function getRoles(): array
    {
        return $this->getAllRelated(self::RELATION_ROLES);
    }

    /**
     * @return \BetaKiller\Model\UserInterface[]
     */
    public function getDisabledUsers(): array
    {
        return $this->getAllRelated(self::RELATION_USERS_OFF);
    }

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function markAsSystem(): NotificationGroupInterface
    {
        return $this->setIsSystem(true);
    }

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function markAsRegular(): NotificationGroupInterface
    {
        return $this->setIsSystem(false);
    }

    /**
     * @return bool
     */
    public function isSystem(): bool
    {
        return (bool)$this->get(self::COL_IS_SYSTEM);
    }

    /**
     * Returns name of I18n key to proceed
     *
     * @return string
     */
    public function getI18nKeyName(): string
    {
        return 'notification-group.'.$this->getCodename();
    }

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function setPlace(int $value): NotificationGroupInterface
    {
        $this->set(self::COL_PLACE, $value);

        return $this;
    }

    /**
     * @return int
     */
    public function getPlace(): int
    {
        return (int)$this->get(self::COL_PLACE);
    }

    private function setIsSystem(bool $value): NotificationGroupInterface
    {
        $this->set(self::COL_IS_SYSTEM, $value);

        return $this;
    }
}
