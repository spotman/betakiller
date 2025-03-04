<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Auth\AuthorizationRequiredException;
use BetaKiller\Exception\DomainException;
use BetaKiller\MessageBus\RestrictionTargetInterface;
use BetaKiller\Task\AbstractTask;
use DateTimeImmutable;

/**
 * Class User
 *
 * @package BetaKiller\Model
 */
class User extends AbstractCreatedAt implements UserInterface
{
    use HasWorkflowStateOrmModelTrait;

    public const TABLE_NAME            = 'users';
    public const COL_ID                = 'id';
    public const COL_STATUS_ID         = 'status_id';
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

    public const  REL_LANGUAGE  = 'language';
    public const  REL_ROLES     = 'roles';
    public const  REL_SESSIONS  = 'sessions';

    public const  DEFAULT_IP   = '127.0.0.1';
    public const  CLI_USERNAME = 'minion';

    private array $cachedRoles = [];

    protected function configure(): void
    {
        $this->_table_name       = static::TABLE_NAME;
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
        return parent::rules() + [
                self::COL_STATUS_ID       => [
                    ['max_length', [':value', 1]],
                ],
                self::COL_EMAIL           => $this->getColumnRulesEmail(),
                self::COL_USERNAME        => $this->getColumnRulesUsername(),
                self::COL_PASSWORD        => $this->getColumnRulesPassword(),
                self::COL_PHONE           => $this->getColumnRulesPhone(),
                self::COL_FIRST_NAME      => $this->getColumnRulesFirstName(),
                self::COL_LAST_NAME       => $this->getColumnRulesLastName(),
                self::COL_CREATED_FROM_IP => $this->getColumnRulesCreatedFromIp(),
                self::COL_LANGUAGE_ID     => [
                    ['max_length', [':value', 11]],
                ],
//                self::COL_FIRST_NAME      => [
//                    ['max_length', [':value', 32]],
//                ],
//                self::COL_LAST_NAME       => [
//                    ['max_length', [':value', 32]],
//                ],
//                self::COL_MIDDLE_NAME     => [
//                    ['max_length', [':value', 32]],
//                ],
                self::COL_NOTIFY_BY_EMAIL => [
                    ['max_length', [':value', 1]],
                ],
                self::COL_LOGINS          => [
                    ['max_length', [':value', 10]],
                ],
                self::COL_LAST_LOGIN      => [
                    ['max_length', [':value', 10]],
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
    public function isMinion(): bool
    {
        return $this->hasUsername() && $this->getUsername() === self::CLI_USERNAME;
    }

    public function isAdmin(): bool
    {
        return $this->hasRoleName(RoleInterface::ADMIN_PANEL);
    }

    /**
     * @return bool
     */
    public function isEmailConfirmed(): bool
    {
        return $this->isInWorkflowState(UserState::EMAIL_CONFIRMED);
    }

    /**
     * @return bool
     */
    public function isBlocked(): bool
    {
        return $this->isInWorkflowState(UserState::BLOCKED);
    }

    /**
     * @return bool
     */
    public function isSuspended(): bool
    {
        return $this->isInWorkflowState(UserState::SUSPENDED);
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
     * @return bool
     */
    public function hasUsername(): bool
    {
        return (bool)$this->get(self::COL_USERNAME);
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
        return $this->hasRoleName(RoleInterface::DEVELOPER);
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
    final public function getRoles(): array
    {
        return $this->cachedRoles ?: $this->cachedRoles = $this->fetchRoles();
    }

    /**
     * @return \BetaKiller\Model\RoleInterface[]
     */
    protected function fetchRoles(): array
    {
        return $this->getAllRelated(self::REL_ROLES);
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

        return $this->isInWorkflowStates([
            UserState::CREATED,
            UserState::EMAIL_CONFIRMED,
            UserState::EMAIL_CHANGED,
            UserState::RESUMED,
        ]);
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

    public function equalsTo(RestrictionTargetInterface $target): bool
    {
        if (!$target instanceof self) {
            throw new DomainException('Restriction target must be instance of UserInterface');
        }

        return $this->isEqualTo($target);
    }

    public static function isEmailEnabled(): bool
    {
        return true;
    }

    public static function isEmailRequired(): bool
    {
        return true;
    }

    public static function isEmailUniqueEnabled(): bool
    {
        return true;
    }

    public static function isEmailRegexEnabled(): bool
    {
        return true;
    }

    private function getColumnRulesEmail(): array
    {
        if (!static::isEmailEnabled()) {
            return [];
        }

        $rules = [];

        if (static::isEmailRequired()) {
            $rules[] = ['not_empty'];
        }

        if (static::isEmailRegexEnabled()) {
            $rules[] = ['email'];
        }

        if (static::isEmailUniqueEnabled()) {
            $rules[] = [[$this, 'unique'], [self::COL_EMAIL, ':value']];
        }

        return $rules;
    }

    public static function isIpAddressEnabled(): bool
    {
        return true;
    }

    public static function isIpAddressRequired(): bool
    {
        return true;
    }

    private function getColumnRulesCreatedFromIp(): array
    {
        if (!static::isIpAddressEnabled()) {
            return [];
        }

        $rules = [
            ['max_length', [':value', 46]], // @see https://stackoverflow.com/a/7477384
//            ['ip', [':value', true]], // Allow local IPs (not working with local dev)
        ];

        if (static::isIpAddressRequired()) {
            $rules[] = ['not_empty'];
        }

        return $rules;
    }

    public static function isUsernameEnabled(): bool
    {
        return true;
    }

    public static function isUsernameRequired(): bool
    {
        return false;
    }

    public static function isUsernameUniqueEnabled(): bool
    {
        return true;
    }

    private function getColumnRulesUsername(): array
    {
        if (!static::isUsernameEnabled()) {
            return [];
        }

        $rules = [
            ['max_length', [':value', 41]],
        ];

        if (static::isUsernameRequired()) {
            $rules[] = ['not_empty'];
        }

        if (static::isUsernameUniqueEnabled()) {
            $rules[] = [[$this, 'unique'], [self::COL_USERNAME, ':value']];
        }

        return $rules;
    }

    public static function isPasswordEnabled(): bool
    {
        return true;
    }

    public static function isPasswordRequired(): bool
    {
        return false;
    }

    public static function isPasswordUniqueEnabled(): bool
    {
        return false;
    }

    private function getColumnRulesPassword(): array
    {
        if (!static::isPasswordEnabled()) {
            return [];
        }

        $rules = [
            ['max_length', [':value', 64]],
        ];

        if (static::isPasswordRequired()) {
            $rules[] = ['not_empty'];
        }

        if (static::isPasswordUniqueEnabled()) {
            $rules[] = [[$this, 'unique'], [self::COL_PASSWORD, ':value']];
        }

        return $rules;
    }

    public static function isPhoneEnabled(): bool
    {
        return true;
    }

    public static function isPhoneRequired(): bool
    {
        return false;
    }

    public static function isPhoneUniqueEnabled(): bool
    {
        return true;
    }

    private function getColumnRulesPhone(): array
    {
        if (!static::isPhoneEnabled()) {
            return [];
        }

        $rules = [
            ['max_length', [':value', 32]],
        ];

        if (static::isPhoneRequired()) {
            $rules[] = ['not_empty'];
        }

        if (static::isPhoneUniqueEnabled()) {
            $rules[] = [[$this, 'unique'], [self::COL_PHONE, ':value']];
        }

        return $rules;
    }

    public static function isFirstNameEnabled(): bool
    {
        return true;
    }

    public static function isFirstNameRequired(): bool
    {
        return true;
    }

    private function getColumnRulesFirstName(): array
    {
        if (!static::isFirstNameEnabled()) {
            return [];
        }

        $rules = [
            ['max_length', [':value', 16]],
        ];

        if (static::isFirstNameRequired()) {
            $rules[] = ['not_empty'];
        }

        return $rules;
    }

    public static function isLastNameEnabled(): bool
    {
        return true;
    }

    public static function isLastNameRequired(): bool
    {
        return true;
    }

    private function getColumnRulesLastName(): array
    {
        if (!static::isLastNameEnabled()) {
            return [];
        }

        $rules = [
            ['max_length', [':value', 16]],
        ];

        if (static::islastNameRequired()) {
            $rules[] = ['not_empty'];
        }

        return $rules;
    }

    protected function hasRoleName(string $name): bool
    {
        foreach ($this->getRoles() as $role) {
            if ($role->getName() === $name) {
                return true;
            }
        }

        return false;
    }
}
