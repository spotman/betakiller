<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Auth\AuthorizationRequiredException;
use BetaKiller\Exception\DomainException;
use BetaKiller\Workflow\HasWorkflowStateModelOrmTrait;
use DateTimeImmutable;

/**
 * Class User
 *
 * @package BetaKiller\Model
 * @method UserStateInterface getWorkflowState()
 */
class User extends \ORM implements UserInterface
{
    use HasWorkflowStateModelOrmTrait;

    public const TABLE_NAME          = 'users';
    public const COL_ID              = 'id';
    public const COL_STATUS_ID       = 'status_id';
    public const COL_CREATED_AT      = 'created_at';
    public const COL_USERNAME        = 'username';
    public const COL_PASSWORD        = 'password';
    public const COL_LANGUAGE_ID     = 'language_id';
    public const COL_FIRST_NAME      = 'first_name';
    public const COL_LAST_NAME       = 'last_name';
    public const COL_MIDDLE_NAME     = 'middle_name';
    public const COL_EMAIL           = 'email';
    public const COL_PHONE           = 'phone';
    public const COL_NOTIFY_BY_EMAIL = 'notify_by_email';
    public const COL_LOGINS          = 'logins';
    public const COL_LAST_LOGIN      = 'last_login';
    public const COL_CREATED_FROM_IP = 'created_from_ip';

    public const  REL_LANGUAGE = 'language';

    protected $allUserRolesNames = [];

    protected function configure(): void
    {
        $this->_table_name       = self::TABLE_NAME;
        $this->_reload_on_wakeup = true;

        $this->belongs_to([
            self::REL_LANGUAGE => [
                'model'       => Language::getModelName(),
                'foreign_key' => self::COL_LANGUAGE_ID,
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

        $this->configureWorkflowStateRelation();

        $this->load_with([
            self::REL_LANGUAGE,
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function getWorkflowStateModelName(): string
    {
        return UserState::getModelName();
    }

    /**
     * @inheritDoc
     */
    public static function getWorkflowStateForeignKey(): string
    {
        return self::COL_STATUS_ID;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            self::COL_STATUS_ID       => [
                ['max_length', [':value', 1]],
            ],
            self::COL_EMAIL           => [
                ['not_empty'],
                ['email'],
                [[$this, 'unique'], ['email', ':value']],
            ],
            self::COL_USERNAME        => [
//                ['not_empty'],
                ['max_length', [':value', 41]],
                [[$this, 'unique'], ['username', ':value']],
            ],
            self::COL_PASSWORD        => [
//                ['not_empty'],
                ['max_length', [':value', 64]],
            ],
            self::COL_LANGUAGE_ID     => [
                ['max_length', [':value', 11]],
            ],
            self::COL_FIRST_NAME      => [
                ['max_length', [':value', 32]],
            ],
            self::COL_LAST_NAME       => [
                ['max_length', [':value', 32]],
            ],
            self::COL_MIDDLE_NAME     => [
                ['max_length', [':value', 32]],
            ],
            self::COL_PHONE           => [
                ['max_length', [':value', 32]],
            ],
            self::COL_NOTIFY_BY_EMAIL => [
                ['max_length', [':value', 1]],
            ],
            self::COL_CREATED_AT      => [
                ['not_empty'],
                ['date'],
            ],
            self::COL_LOGINS          => [
                ['max_length', [':value', 10]],
            ],
            self::COL_LAST_LOGIN      => [
                ['max_length', [':value', 10]],
            ],
            self::COL_CREATED_FROM_IP => [
                ['not_empty'],
//                ['ip', [':value', true]], // Allow local IPs (not working with local dev)
                ['max_length', [':value', 46]], // @see https://stackoverflow.com/a/7477384
            ],
        ];
    }
//
//    /**
//     * Labels for fields in this model
//     *
//     * @return array Labels
//     */
//    public function labels(): array
//    {
//        return [
//            'username' => 'username',
//            'email'    => 'email address',
//            'password' => 'password',
//        ];
//    }

    /**
     * @return bool
     */
    public function isEmailConfirmed(): bool
    {
        return $this->getWorkflowState()->isConfirmed();
    }

    /**
     * @return bool
     */
    public function isBlocked(): bool
    {
        return $this->getWorkflowState()->isBlocked();
    }

    /**
     * @return bool
     */
    public function isSuspended(): bool
    {
        return $this->getWorkflowState()->isSuspended();
    }

    /**
     * @return bool
     */
    public function isRegistrationClaimed(): bool
    {
        return $this->getWorkflowState()->isClaimed();
    }

    /**
     * @param \DateTimeInterface $value [optional]
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setCreatedAt(\DateTimeInterface $value = null): UserInterface
    {
        $value = $value ?: new \DateTimeImmutable;
        $this->set_datetime_column_value(self::COL_CREATED_AT, $value);

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     * @throws \BetaKiller\Exception\DomainException
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        $createdAt = $this->get_datetime_column_value(self::COL_CREATED_AT);

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
        return $this->set(self::COL_USERNAME, $value);
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return (string)$this->get(self::COL_USERNAME);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setPassword(string $value): UserInterface
    {
        return $this->set(self::COL_PASSWORD, $value);
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return (string)$this->get(self::COL_PASSWORD);
    }

    /**
     * @return bool
     */
    public function hasPassword(): bool
    {
        return (bool)$this->get(self::COL_PASSWORD);
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
     * @return bool
     */
    public function hasAdminRole(): bool
    {
        // This role is not assigned directly but through inheritance
        return $this->hasRoleName(RoleInterface::ADMIN_PANEL);
    }

    /**
     * @return bool
     */
    public function hasDeveloperRole(): bool
    {
        return $this->hasRoleName(RoleInterface::DEVELOPER);
    }

    /**
     * @param RoleInterface $role
     *
     * @return bool
     * @todo Переписать на кешированный ACL ибо слишком затратно делать запрос в БД на проверку роли
     *
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
     * Returns true if user has any of provided role assigned
     *
     * @param \BetaKiller\Model\RoleInterface[] $roles
     *
     * @return bool
     */
    public function hasAnyOfRoles(array $roles): bool
    {
        $rolesNames = array_map(static function(RoleInterface $role) {
            return $role->getName();
        }, $roles);

        return $this->hasAnyOfRolesNames($rolesNames);
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
        return $this->set(self::COL_LANGUAGE_ID, $languageModel);
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
        return ($this->loaded() && !$this->getWorkflowState()->isBlocked());
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
        return $this->set(self::COL_FIRST_NAME, $value);
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return (string)$this->get(self::COL_FIRST_NAME);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setLastName(string $value): UserInterface
    {
        return $this->set(self::COL_LAST_NAME, $value);
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return (string)$this->get(self::COL_LAST_NAME);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setMiddleName(string $value): UserInterface
    {
        return $this->set(self::COL_MIDDLE_NAME, $value);
    }

    /**
     * @return string
     */
    public function getMiddleName(): string
    {
        return (string)$this->get(self::COL_MIDDLE_NAME);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setEmail(string $value): UserInterface
    {
        return $this->set(self::COL_EMAIL, $value);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return (string)$this->get(self::COL_EMAIL);
    }

    /**
     * @param string $number
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setPhone(string $number): UserInterface
    {
        return $this->set(self::COL_PHONE, $number);
    }

    /**
     * @inheritDoc
     */
    public function hasPhoneDefined(): bool
    {
        return (string)$this->get(self::COL_PHONE) !== '';
    }

    /**
     * Возвращает основной номер телефона
     *
     * @return string
     */
    public function getPhone(): string
    {
        return (string)$this->get(self::COL_PHONE);
    }

    /**
     * @return bool
     */
    public function isEmailNotificationAllowed(): bool
    {
        return (bool)$this->get(self::COL_NOTIFY_BY_EMAIL);
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
        $this->set(self::COL_NOTIFY_BY_EMAIL, true);
    }

    public function disableEmailNotification(): void
    {
        $this->set(self::COL_NOTIFY_BY_EMAIL, false);
    }

    /**
     * @return array
     */
    public function as_array(): array
    {
        return [
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
     * @return void
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
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
        return (string)$this->get(self::COL_CREATED_FROM_IP);
    }

    /**
     * @param string $ip
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setCreatedFromIP(string $ip): UserInterface
    {
        $this->set(self::COL_CREATED_FROM_IP, $ip);

        return $this;
    }
}
