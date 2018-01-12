<?php
namespace BetaKiller\Model;

class User extends \Model_Auth_User implements UserInterface
{
    protected $allUserRolesIDs = [];

    protected function _initialize(): void
    {
        $this->_table_name       = 'users';
        $this->_reload_on_wakeup = false;

        $this->belongs_to([
            'language' => [
                'model'       => 'Language',
                'foreign_key' => 'language_id',
            ],
        ]);

        $this->has_many([
            'ulogins' => [],
        ]);

        $this->load_with(['language']);

        parent::_initialize();
    }

    /**
     * @return Role
     * @throws \Kohana_Exception
     */
    protected function getRolesRelation(): Role
    {
        return $this->get('roles');
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     * @throws \Kohana_Exception
     */
    public function setUsername(string $value): UserInterface
    {
        return $this->set('username', $value);
    }

    /**
     * @return string
     * @throws \Kohana_Exception
     */
    public function getUsername(): string
    {
        return $this->get('username');
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     * @throws \Kohana_Exception
     */
    public function setPassword(string $value): UserInterface
    {
        return $this->set('password', $value);
    }

    /**
     * @return string
     * @throws \Kohana_Exception
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
        return $this->has('roles', $role);
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
     * Get all user`s roles IDs
     *
     * @return array
     */
    public function getAllUserRolesIDs(): array
    {
        // Caching coz it is very heavy operation without MPTT
        if (!$this->allUserRolesIDs) {
            $this->allUserRolesIDs = $this->fetchAllUserRolesIDs();
        }

        return $this->allUserRolesIDs;
    }

    protected function fetchAllUserRolesIDs()
    {
        $rolesIDs = [];

        foreach ($this->getRolesRelation()->get_all() as $role) {
            $rolesIDs[] = $role->getID();

            /** @var \BetaKiller\Model\RoleInterface $parent */
            foreach ($role->getAllParents() as $parent) {
                $rolesIDs[] = $parent->getID();
            }
        }

        return array_unique($rolesIDs);
    }

    /**
     * Returns user`s language name
     *
     * @return string|null
     * @throws \Kohana_Exception
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
     * @throws \Kohana_Exception
     */
    public function getLanguage(): \BetaKiller\Model\Language
    {
        return $this->get('language');
    }

    /**
     * Search for user by username or e-mail
     *
     * @param string $usernameOrEmail
     *
     * @return UserInterface|null
     * @throws \Kohana_Exception
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
     * @throws \Auth_Exception_Inactive
     * @throws \Kohana_Exception
     */
    protected function checkIsActive(): void
    {
        // Проверяем активен ли аккаунт
        if (!$this->isActive()) {
            throw new \Auth_Exception_Inactive;
        }
    }

    /**
     * @throws \Auth_Exception_Inactive
     * @throws \Kohana_Exception
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
     * @throws \Kohana_Exception
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
     * @throws \Kohana_Exception
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

    /**
     * @throws \Kohana_Exception
     */
    public function enableEmailNotification(): void
    {
        $this->set('notify_by_email', true);
    }

    /**
     * @throws \Kohana_Exception
     */
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
     * @throws \Kohana_Exception
     */
    public function getAccessControlIdentity(): string
    {
        return $this->getUsername();
    }

    /**
     * @return RoleInterface[]|\Traversable
     * @throws \Kohana_Exception
     */
    public function getAccessControlRoles()
    {
        return $this->getRolesRelation()->get_all();
    }

    /**
     * Forces authorization if user is not logged in
     *
     * @throws \HTTP_Exception_401
     * @return void
     */
    public function forceAuthorization(): void
    {
        if ($this->isGuest()) {
            throw new \HTTP_Exception_401();
        }
    }

    protected function getSerializableProperties()
    {
        return array_merge(parent::getSerializableProperties(), [
            'allUserRolesIDs',
        ]);
    }
}
