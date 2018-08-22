<?php
namespace BetaKiller\Model;

use BetaKiller\Notification\NotificationUserInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use Spotman\Acl\AclUserInterface;

interface UserInterface extends AbstractEntityInterface, OrmInterface, NotificationUserInterface, AclUserInterface
{
    // Extended methods

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
     * @return $this
     */
    public function setPassword(string $value): UserInterface;

    /**
     * @return string
     */
    public function getPassword(): string;

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
     * @param RoleInterface $role
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
     * @return string|null
     */
    public function getLanguageName(): ?string;

    /**
     * @return \BetaKiller\Model\Language|null
     */
    public function getLanguage(): ?Language;

    /**
     * Search for user by username or e-mail
     *
     * @param string $usernameOrEmail
     *
     * @return UserInterface|null
     */
    public function searchBy(string $usernameOrEmail): ?UserInterface;

    /**
     * @return void
     */
    public function beforeSignIn(): void;

    /**
     * @return void
     * @throws \BetaKiller\Auth\WrongIPException
     */
    public function afterAutoLogin(): void;

    /**
     * @return void
     */
    public function beforeSignOut(): void;

    /**
     * Returns TRUE, if user account is switched on
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Returns true if current user is guest
     *
     * @return bool
     */
    public function isGuest(): bool;

    /**
     * @return string
     */
    public function getFirstName(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setFirstName(string $value): UserInterface;

    /**
     * @return string
     */
    public function getLastName(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setLastName(string $value): UserInterface;

    /**
     * @return string
     */
    public function getMiddleName(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setMiddleName(string $value): UserInterface;

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setEmail(string $value): UserInterface;

    /**
     * Returns primary phone number
     *
     * @return string
     */
    public function getPhone(): string;

    /**
     * @param string $number
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setPhone(string $number): UserInterface;

    /**
     * Forces authorization if user is not logged in
     *
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @return void
     */
    public function forceAuthorization(): void;
}
