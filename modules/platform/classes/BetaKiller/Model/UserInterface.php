<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Notification\NotificationUserInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use DateTimeImmutable;
use Spotman\Acl\AclUserInterface;

interface UserInterface extends AbstractEntityInterface, OrmInterface, NotificationUserInterface, AclUserInterface
{
    public function completeLogin(): void;

    /**
     * @param \DateTimeInterface $value [optional]
     *
     * @return \Worknector\Model\UserInterface
     */
    public function setCreatedAt(\DateTimeInterface $value = null): UserInterface;

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
     * Returns user`s language name
     *
     * @return string
     */
    public function getLanguageName(): string;

    /**
     * @param \BetaKiller\Model\LanguageInterface $languageModel
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setLanguage(LanguageInterface $languageModel): UserInterface;

    /**
     * @return null|\BetaKiller\Model\LanguageInterface
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
}
