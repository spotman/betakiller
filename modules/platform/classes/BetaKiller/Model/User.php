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

    public const TABLE_NAME            = 'users';
    public const COL_ID                = 'id';
    public const COL_STATUS_ID         = 'status_id';
    public const COL_CREATED_AT        = 'created_at';
    public const COL_USERNAME          = 'username';
    public const COL_PASSWORD          = 'password';
    public const COL_LANGUAGE_ID       = 'language_id';
    public const COL_FIRST_NAME        = 'first_name';
    public const COL_LAST_NAME         = 'last_name';
    public const COL_MIDDLE_NAME       = 'middle_name';
    public const COL_EMAIL             = 'email';
    public const COL_PHONE             = 'phone';
    public const COL_IS_PHONE_VERIFIED = 'is_phone_verified';
    public const COL_NOTIFY_BY_EMAIL   = 'notify_by_email';
    public const COL_LOGINS            = 'logins';
    public const COL_LAST_LOGIN        = 'last_login';
    public const COL_CREATED_FROM_IP   = 'created_from_ip';
    public const COL_IS_CLAIMED        = 'is_reg_claimed';

    public const  REL_LANGUAGE = 'language';
    public const  REL_ROLES    = 'roles';
    public const  REL_SESSIONS = 'sessions';

    /**
     * @var array
     * @deprecated Remove after several deployments since 4 July 2021
     */
    protected array $allUserRolesNames = [];

    private array $cachedRoles = [];

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
            self::REL_SESSIONS => [
                'model'       => UserSession::getModelName(),
                'foreign_key' => 'user_id',
            ],

            self::REL_ROLES => [
                'model'       => Role::getModelName(),
                'through'     => 'roles_users',
                'foreign_key' => 'user_id',
                'far_key'     => 'role_id',
            ],
        ]);

        $this->configureWorkflowStateRelation();

        $this->load_with([
            self::REL_LANGUAGE,
            self::REL_ROLES,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function isCachingAllowed(): bool
    {
        // User-related Entity
        return false;
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

    public function markAsRegistrationClaimed(): void
    {
        $this->set(self::COL_IS_CLAIMED, true);
    }

    /**
     * @return bool
     */
    public function isRegistrationClaimed(): bool
    {
        return (bool)$this->get(self::COL_IS_CLAIMED);
    }

    /**
     * @param \DateTimeInterface|null $value [optional]
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setCreatedAt(\DateTimeInterface $value = null): UserInterface
    {
        $value = $value ?: new DateTimeImmutable;
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
        // Lowercase to prevent collisions
        $value = \mb_strtolower($value);

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
     * Returns true if current user is guest
     *
     * @return bool
     */
    public function isGuest(): bool
    {
        return ($this instanceof GuestUserInterface);
    }

    /**
     * @inheritDoc
     */
    public function isDeveloper(): bool
    {
        foreach ($this->getRoles() as $role) {
            if ($role->getName() === RoleInterface::DEVELOPER) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function addRole(RoleInterface $role): UserInterface
    {
        // Reset cached roles
        $this->cachedRoles = [];

        return $this->add(self::REL_ROLES, $role);
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        return $this->cachedRoles ?: $this->cachedRoles = $this->getAllRelated(self::REL_ROLES);
    }

    /**
     * @inheritDoc
     */
    public function getAllRoles(): array
    {
        $roles = [];

        foreach ($this->getRoles() as $role) {
            $roles[$role->getName()] = $role;

            /** @var \BetaKiller\Model\RoleInterface $parent */
            foreach ($role->getAllParents() as $parent) {
                $roles[$parent->getName()] = $parent;
            }
        }

        return \array_values($roles);
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
        return $this->getRelatedEntity(self::REL_LANGUAGE);
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
        if (!$this->loaded()) {
            return false;
        }

        $state = $this->getWorkflowState();

        return $state->isCreated() || $state->isConfirmed() || $state->isEmailChanged() || $state->isResumed();
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
        // Lowercase to prevent collisions
        $value = \mb_strtolower($value);

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
        if ($this->hasPhoneDefined() && $this->getPhone() !== $number) {
            // Changed phone => needs verification
            $this->set(self::COL_IS_PHONE_VERIFIED, false);
        }

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
     * @inheritDoc
     */
    public function markPhoneAsVerified(): void
    {
        $this->set(self::COL_IS_PHONE_VERIFIED, true);
    }

    /**
     * @inheritDoc
     */
    public function isPhoneVerified(): bool
    {
        return (bool)$this->get(self::COL_IS_PHONE_VERIFIED);
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
        return $this->getRoles();
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

    /**
     * @inheritDoc
     */
    public function getLastLoggedIn(): ?DateTimeImmutable
    {
        $ts = (int)$this->get(self::COL_LAST_LOGIN);

        return $ts
            ? (new DateTimeImmutable())->setTimestamp($ts)
            : null;
    }
}
