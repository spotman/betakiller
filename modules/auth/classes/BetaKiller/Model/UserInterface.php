<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Notification\TargetInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Spotman\Acl\AclUserInterface;

interface UserInterface extends DispatchableEntityInterface, OrmInterface, TargetInterface, AclUserInterface
{
    public function completeLogin(): void;

    /**
     * @param \BetaKiller\Model\UserStatusInterface $userStatusModel
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setStatus(UserStatusInterface $userStatusModel): UserInterface;

    /**
     * @return \BetaKiller\Model\UserStatusInterface
     */
    public function getStatus(): UserStatusInterface;

    /**
     * @return bool
     */
    public function isEmailConfirmed(): bool;

    /**
     * @return bool
     */
    public function isBlocked(): bool;

    /**
     * @return bool
     */
    public function isSuspended(): bool;

    /**
     * @param \DateTimeInterface $value [optional]
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setCreatedAt(DateTimeInterface $value = null): UserInterface;

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setUsername(string $value): UserInterface;

    /**
     * @return string
     */
    public function getUsername(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setPassword(string $value): UserInterface;

    /**
     * @return string
     */
    public function getPassword(): string;

    /**
     * @return bool
     */
    public function hasPassword(): bool;

    /**
     * Returns true if current user is guest
     *
     * @return bool
     */
    public function isGuest(): bool;

    /**
     * @param RoleInterface $role
     *
     * @return bool
     */
    public function hasRole(RoleInterface $role): bool;

    /**
     * @param string $role
     *
     * @return bool
     */
    public function hasRoleName(string $role): bool;

    /**
     * Returns true if user has any of provided role assigned
     *
     * @param string[] $roles
     *
     * @return bool
     */
    public function hasAnyOfRolesNames(array $roles): bool;

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function addRole(RoleInterface $role): UserInterface;

    /**
     * Get all user`s roles names (include parent roles)
     *
     * @return string[]
     */
    public function getAllUserRolesNames(): array;

    /**
     * @param \BetaKiller\Model\LanguageInterface $languageModel
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setLanguage(LanguageInterface $languageModel): UserInterface;

    /**
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function getLanguage(): LanguageInterface;

    /**
     * Returns TRUE, if user account is switched on
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @return string
     */
    public function getFullName(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setFirstName(string $value): UserInterface;

    /**
     * @return string
     */
    public function getFirstName(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setLastName(string $value): UserInterface;

    /**
     * @return string
     */
    public function getLastName(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setMiddleName(string $value): UserInterface;

    /**
     * @return string
     */
    public function getMiddleName(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setEmail(string $value): UserInterface;

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * @param string $number
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setPhone(string $number): UserInterface;

    /**
     * Returns primary phone number
     *
     * @return string
     */
    public function getPhone(): string;

    /**
     * @return bool
     */
    public function isEmailNotificationAllowed(): bool;

    /**
     * @return bool
     */
    public function isOnlineNotificationAllowed(): bool;

    public function enableEmailNotification(): void;

    public function disableEmailNotification(): void;

    /**
     * @return string
     */
    public function getAccessControlIdentity(): string;

    /**
     * @return RoleInterface[]
     */
    public function getAccessControlRoles(): array;

    /**
     * Forces authorization if user is not logged in
     *
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @return void
     */
    public function forceAuthorization(): void;

    /**
     * @return string
     */
    public function getCreatedFromIP(): string;

    /**
     * @param string $ip
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setCreatedFromIP(string $ip): UserInterface;
}
