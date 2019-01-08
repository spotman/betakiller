<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Auth\AuthorizationRequiredException;
use BetaKiller\Exception\DomainException;
use DateTimeImmutable;

class User extends \ORM implements UserInterface
{
    public const TABLE_NAME                  = 'users';
    public const TABLE_FIELD_STATUS_ID       = 'status_id';
    public const TABLE_FIELD_CREATED_AT      = 'created_at';
    public const TABLE_FIELD_USERNAME        = 'username';
    public const TABLE_FIELD_PASSWORD        = 'password';
    public const TABLE_FIELD_LANGUAGE_ID     = 'language_id';
    public const TABLE_FIELD_FIRST_NAME      = 'first_name';
    public const TABLE_FIELD_LAST_NAME       = 'last_name';
    public const TABLE_FIELD_MIDDLE_NAME     = 'middle_name';
    public const TABLE_FIELD_EMAIL           = 'email';
    public const TABLE_FIELD_PHONE           = 'phone';
    public const TABLE_FIELD_NOTIFY_BY_EMAIL = 'notify_by_email';
    public const TABLE_FIELD_LOGINS          = 'logins';
    public const TABLE_FIELD_LAST_LOGIN      = 'last_login';
    public const TABLE_FIELD_CREATED_FROM_IP = 'created_from_ip';

    protected $allUserRolesNames = [];

    protected function configure(): void
    {
        $this->_table_name       = self::TABLE_NAME;
        $this->_reload_on_wakeup = true;

        $this->belongs_to([
            'status'   => [
                'model'       => 'UserStatus',
                'foreign_key' => self::TABLE_FIELD_STATUS_ID,
            ],
            'language' => [
                'model'       => 'Language',
                'foreign_key' => self::TABLE_FIELD_LANGUAGE_ID,
            ],
        ]);

        $this->has_many([
            'sessions' => [
                'model'       => 'UserSession',
                'foreign_key' => 'user_id',
            ],
            'roles'    => [
                'model'       => 'Role',
                'through'     => 'roles_users',
                'foreign_key' => 'user_id',
            ],
        ]);

        $this->load_with([
            'status',
            'language',
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            self::TABLE_FIELD_STATUS_ID       => [
                ['max_length', [':value', 1]],
            ],
            self::TABLE_FIELD_EMAIL           => [
                ['not_empty'],
                ['email'],
                [[$this, 'unique'], ['email', ':value']],
            ],
            self::TABLE_FIELD_USERNAME        => [
                ['not_empty'],
                ['max_length', [':value', 41]],
                [[$this, 'unique'], ['username', ':value']],
            ],
            self::TABLE_FIELD_PASSWORD        => [
                ['not_empty'],
                ['max_length', [':value', 64]],
            ],
            self::TABLE_FIELD_LANGUAGE_ID     => [
                ['max_length', [':value', 11]],
            ],
            self::TABLE_FIELD_FIRST_NAME      => [
                ['max_length', [':value', 32]],
            ],
            self::TABLE_FIELD_LAST_NAME       => [
                ['max_length', [':value', 32]],
            ],
            self::TABLE_FIELD_MIDDLE_NAME     => [
                ['max_length', [':value', 32]],
            ],
            self::TABLE_FIELD_PHONE           => [
                ['max_length', [':value', 32]],
            ],
            self::TABLE_FIELD_NOTIFY_BY_EMAIL => [
                ['max_length', [':value', 1]],
            ],
            self::TABLE_FIELD_CREATED_AT      => [
                ['not_empty'],
                ['date'],
            ],
            self::TABLE_FIELD_LOGINS          => [
                ['max_length', [':value', 10]],
            ],
            self::TABLE_FIELD_LAST_LOGIN      => [
                ['max_length', [':value', 10]],
            ],
            self::TABLE_FIELD_CREATED_FROM_IP => [
                ['not_empty'],
//                ['ip', [':value', true]], // Allow local IPs (not working with local dev)
                ['max_length', [':value', 46]], // @see https://stackoverflow.com/a/7477384
            ],
        ];
    }

    /**
     * Labels for fields in this model
     *
     * @return array Labels
     */
    public function labels(): array
    {
        return [
            'username' => 'username',
            'email'    => 'email address',
            'password' => 'password',
        ];
    }

    /**
     * @param \BetaKiller\Model\UserStatusInterface $userStatusModel
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setStatus(UserStatusInterface $userStatusModel): UserInterface
    {
        return $this->set('status', $userStatusModel);
    }

    /**
     * @return \BetaKiller\Model\UserStatusInterface
     */
    public function getStatus(): UserStatusInterface
    {
        return $this->getRelatedEntity('status');
    }

    /**
     * @return bool
     */
    public function isEmailConfirmed(): bool
    {
        /**
         * @var \BetaKiller\Model\UserStatusInterface $statusModel
         */
        $statusModel = $this->getStatus();

        return $statusModel->getCodename() !== UserStatus::STATUS_CREATED;
    }

    /**
     * @param \DateTimeInterface $value [optional]
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setCreatedAt(\DateTimeInterface $value = null): UserInterface
    {
        $value = $value ?: new \DateTimeImmutable;
        $this->set_datetime_column_value(self::TABLE_FIELD_CREATED_AT, $value);

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     * @throws \BetaKiller\Exception\DomainException
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        $createdAt = $this->get_datetime_column_value(self::TABLE_FIELD_CREATED_AT);

        if (!$createdAt) {
            throw new DomainException('User::createdAt can not be empty');
        }

        return $createdAt;
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setUsername(string $value): UserInterface
    {
        return $this->set(self::TABLE_FIELD_USERNAME, $value);
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return (string)$this->get(self::TABLE_FIELD_USERNAME);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setPassword(string $value): UserInterface
    {
        return $this->set(self::TABLE_FIELD_PASSWORD, $value);
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return (string)$this->get(self::TABLE_FIELD_PASSWORD);
    }

    /**
     * @return \BetaKiller\Model\Role
     */
    protected function getRolesRelation(): Role
    {
        return $this->get('roles');
    }

    /**
     * Returns true if current user is guest
     *
     * @return bool
     */
    public function isGuest(): bool
    {
        return ($this instanceof GuestUserInterface);
    }

    /**
     * @todo Переписать на кешированный ACL ибо слишком затратно делать запрос в БД на проверку роли
     *
     * @param RoleInterface $role
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
     * @param \BetaKiller\Model\RoleInterface $role
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
     * @return string
     */
    public function getLanguageIsoCode(): string
    {
        return $this->getLanguage()->getIsoCode();
    }

    /**
     * @param \BetaKiller\Model\LanguageInterface $languageModel
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setLanguage(LanguageInterface $languageModel): UserInterface
    {
        return $this->set(self::TABLE_FIELD_LANGUAGE_ID, $languageModel);
    }

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function getLanguage(): LanguageInterface
    {
        return $this->getRelatedEntity('language');
    }

    /**
     * Complete the login for a user by incrementing the logins and saving login timestamp
     *
     * @return void
     */
    public function completeLogin(): void
    {
        // Update the number of logins
        $this->set('logins', new \Database_Expression('logins + 1'));

        // Set the last login date
        $this->set('last_login', time());
    }

    /**
     * Returns TRUE, if user account is switched on
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return ($this->loaded() && !$this->getStatus()->isBlocked());
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setFirstName(string $value): UserInterface
    {
        return $this->set(self::TABLE_FIELD_FIRST_NAME, $value);
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return (string)$this->get(self::TABLE_FIELD_FIRST_NAME);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setLastName(string $value): UserInterface
    {
        return $this->set(self::TABLE_FIELD_LAST_NAME, $value);
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return (string)$this->get(self::TABLE_FIELD_LAST_NAME);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setMiddleName(string $value): UserInterface
    {
        return $this->set(self::TABLE_FIELD_MIDDLE_NAME, $value);
    }

    /**
     * @return string
     */
    public function getMiddleName(): string
    {
        return (string)$this->get(self::TABLE_FIELD_MIDDLE_NAME);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setEmail(string $value): UserInterface
    {
        return $this->set(self::TABLE_FIELD_EMAIL, $value);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return (string)$this->get(self::TABLE_FIELD_EMAIL);
    }

    /**
     * @param string $number
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setPhone(string $number): UserInterface
    {
        return $this->set(self::TABLE_FIELD_PHONE, $number);
    }

    /**
     * Возвращает основной номер телефона
     *
     * @return string
     */
    public function getPhone(): string
    {
        return (string)$this->get(self::TABLE_FIELD_PHONE);
    }

    /**
     * @return bool
     */
    public function isEmailNotificationAllowed(): bool
    {
        return (bool)$this->get(self::TABLE_FIELD_NOTIFY_BY_EMAIL);
    }

    /**
     * @return bool
     */
    public function isOnlineNotificationAllowed(): bool
    {
        // Online notification isn`t ready yet
        return false;
    }

    public function enableEmailNotification(): void
    {
        $this->set(self::TABLE_FIELD_NOTIFY_BY_EMAIL, true);
    }

    public function disableEmailNotification(): void
    {
        $this->set(self::TABLE_FIELD_NOTIFY_BY_EMAIL, false);
    }

    /**
     * @return array
     */
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
     * @return RoleInterface[]
     */
    public function getAccessControlRoles(): array
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

    /**
     * @return array
     */
    protected function getSerializableProperties(): array
    {
        return array_merge(parent::getSerializableProperties(), [
            'allUserRolesNames',
        ]);
    }

    /**
     * @return string
     */
    public function getCreatedFromIP(): string
    {
        return (string)$this->get(self::TABLE_FIELD_CREATED_FROM_IP);
    }

    /**
     * @param string $ip
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setCreatedFromIP(string $ip): UserInterface
    {
        $this->set(self::TABLE_FIELD_CREATED_FROM_IP, $ip);

        return $this;
    }
}
