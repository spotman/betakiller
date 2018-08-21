<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Auth\AuthorizationRequiredException;
use BetaKiller\Auth\InactiveException;

class User extends \Model_Auth_User implements UserInterface
{
    protected $allUserRolesNames = [];

    protected function configure(): void
    {
        $this->_table_name       = 'users';
        $this->_reload_on_wakeup = true;

        $this->belongs_to([
            'language' => [
                'model'       => 'Language',
                'foreign_key' => 'language_id',
            ],
        ]);

        $this->has_many([
            'ulogins'             => [],
            'notification_groups' => [
                'model'       => 'NotificationGroupUser',
                'foreign_key' => NotificationGroupUser::TABLE_FIELD_USER_ID,
            ],

        ]);

        $this->load_with(['language', 'notification_groups']);

        parent::configure();
    }

    /**
     * @return \BetaKiller\Model\NotificationGroupUserInterface
     */
    protected function getNotificationGroupsRelation(): NotificationGroupUserInterface
    {
        return $this->get('notification_groups');
    }

    /**
     * @return NotificationGroupUserInterface[]|\Traversable
     */
    public function getNotificationGroups()
    {
        return $this->getNotificationGroupsRelation()->get_all();
    }

    /**
     * @param NotificationGroupUserInterface $model
     *
     * @return bool
     */
    public function hasNotificationGroup(NotificationGroupUserInterface $model): bool
    {
        return $this->hasNotificationGroupCodename($model->getGroupCodename());
    }

    /**
     * @param string $codename
     *
     * @return bool
     */
    public function hasNotificationGroupCodename(string $codename): bool
    {
        foreach ($this->getNotificationGroups() as $model) {
            if ($model->getGroupCodename() === $codename) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Role
     */
    protected function getRolesRelation(): Role
    {
        return $this->get('roles');
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setUsername(string $value): UserInterface
    {
        return $this->set('username', $value);
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->get('username');
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setPassword(string $value): UserInterface
    {
        return $this->set('password', $value);
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->get('password');
    }

    /**
     * Returns true if current user is guest
     *
     * @return bool
     */
    public function isGuest(): bool
    {
        return ($this instanceof GuestUser);
    }

    /**
     * @todo Переписать на кешированный ACL ибо слишком затратно делать запрос в БД на проверку роли
     *
     * @param RoleInterface|string $role
     *
     * @return bool
     */
    public function hasRole(RoleInterface $role): bool
    {
        return $this->hasRoleName($role->getName());
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    public function hasRoleName(string $role): bool
    {
        foreach ($this->getAllUserRolesNames() as $name) {
            if ($role === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if user has any of provided role assigned
     *
     * @param string[] $roles
     *
     * @return bool
     */
    public function hasAnyOfRolesNames(array $roles): bool
    {
        return (bool)\array_intersect($this->getAllUserRolesNames(), $roles);
    }

    /**
     * @param \BetaKiller\Model\RoleInterface|string $role
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function addRole(RoleInterface $role): UserInterface
    {
        return $this->add('roles', $role);
    }

    /**
     * Get all user`s roles names (include parent roles)
     *
     * @return string[]
     */
    public function getAllUserRolesNames(): array
    {
        // Caching coz it is very heavy operation without MPTT
        if (!$this->allUserRolesNames) {
            $this->allUserRolesNames = $this->fetchAllUserRolesNames();
        }

        return $this->allUserRolesNames;
    }

    protected function fetchAllUserRolesNames(): array
    {
        $rolesNames = [];

        foreach ($this->getRolesRelation()->get_all() as $role) {
            $rolesNames[] = $role->getName();

            /** @var \BetaKiller\Model\RoleInterface $parent */
            foreach ($role->getAllParents() as $parent) {
                $rolesNames[] = $parent->getName();
            }
        }

        return array_unique($rolesNames);
    }

    /**
     * Returns user`s language name
     *
     * @return string|null
     */
    public function getLanguageName(): ?string
    {
        $langModel = $this->getLanguage();

        $lang = ($this->loaded() && $langModel->loaded())
            ? $langModel->getName()
            : null;

        return $lang;
    }

    /**
     * @return \BetaKiller\Model\Language
     */
    public function getLanguage(): Language
    {
        return $this->get('language');
    }

    /**
     * Search for user by username or e-mail
     *
     * @param string $usernameOrEmail
     *
     * @return UserInterface|null
     */
    public function searchBy(string $usernameOrEmail): ?UserInterface
    {
        $user = $this->where($this->unique_key($usernameOrEmail), '=', $usernameOrEmail)->find();

        return $user->loaded() ? $user : null;
    }

    public function beforeSignIn(): void
    {
        $this->checkIsActive();
    }

    /**
     * @throws \BetaKiller\Auth\InactiveException
     */
    protected function checkIsActive(): void
    {
        // Проверяем активен ли аккаунт
        if (!$this->isActive()) {
            throw new InactiveException;
        }
    }

    /**
     * @throws \BetaKiller\Auth\InactiveException
     */
    public function afterAutoLogin(): void
    {
        $this->checkIsActive();
    }

    public function beforeSignOut(): void
    {
        // Empty by default
    }

    /**
     * Returns TRUE, if user account is switched on
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return ($this->loaded() && $this->get('is_active'));
    }

    public function getFullName(): string
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    public function getFirstName(): string
    {
        return (string)$this->get('first_name');
    }

    public function setFirstName(string $value): UserInterface
    {
        return $this->set('first_name', $value);
    }

    public function getLastName(): string
    {
        return (string)$this->get('last_name');
    }

    public function setLastName(string $value): UserInterface
    {
        return $this->set('last_name', $value);
    }

    /**
     * @return string
     */
    public function getMiddleName(): string
    {
        return (string)$this->get('middle_name');
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setMiddleName(string $value): UserInterface
    {
        return $this->set('middle_name', $value);
    }


    public function getEmail(): string
    {
        return $this->get('email');
    }

    public function setEmail(string $value): UserInterface
    {
        return $this->set('email', $value);
    }

    /**
     * Возвращает основной номер телефона
     *
     * @return string
     */
    public function getPhone(): string
    {
        return (string)$this->get('phone');
    }

    public function setPhone(string $number): UserInterface
    {
        return $this->set('phone', $number);
    }

    public function isEmailNotificationAllowed(): bool
    {
        return (bool)$this->get('notify_by_email');
    }

    public function isOnlineNotificationAllowed(): bool
    {
        // Online notification isn`t ready yet
        return false;
    }

    public function enableEmailNotification(): void
    {
        $this->set('notify_by_email', true);
    }

    public function disableEmailNotification(): void
    {
        $this->set('notify_by_email', false);
    }

    public function as_array(): array
    {
        return [
            'id'        => $this->getID(),
            'username'  => $this->getUsername(),
            'email'     => $this->getEmail(),
            'firstName' => $this->getFirstName(),
            'lastName'  => $this->getLastName(),
            'phone'     => $this->getPhone(),
        ];
    }

    /**
     * @return string
     */
    public function getAccessControlIdentity(): string
    {
        return $this->getUsername();
    }

    /**
     * @return RoleInterface[]|\Traversable
     */
    public function getAccessControlRoles()
    {
        return $this->getRolesRelation()->get_all();
    }

    /**
     * Forces authorization if user is not logged in
     *
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @return void
     */
    public function forceAuthorization(): void
    {
        if ($this->isGuest()) {
            throw new AuthorizationRequiredException();
        }
    }

    protected function getSerializableProperties()
    {
        return array_merge(parent::getSerializableProperties(), [
            'allUserRolesNames',
        ]);
    }
}
