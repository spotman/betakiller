<?php
namespace BetaKiller\Model;

use BetaKiller\Notification\NotificationUserInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use Spotman\Acl\AclUserInterface;

interface UserInterface extends AbstractEntityInterface, OrmInterface, NotificationUserInterface, AclUserInterface
{
    // Auth_ORM methods
    public function complete_login();


    // Extended methods

    /**
     * @param string $value
     * @return $this
     */
    public function setUsername(string $value);

    /**
     * @return string
     */
    public function getUsername(): string;

    /**
     * @param string $value
     * @return $this
     */
    public function setPassword(string $value);

    /**
     * @return string
     */
    public function getPassword(): string;

    /**
     * @return bool
     */
    public function isDeveloper(): bool;

    /**
     * @return bool
     * @deprecated Use ACL resources instead
     */
    public function isModerator(): bool;

    /**
     * @param RoleInterface|string $role
     * @return bool
     */
    public function hasRole(RoleInterface $role): bool;

    /**
     * @param string|RoleInterface $role
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function addRole(RoleInterface $role): UserInterface;

    /**
     * Get all user`s roles IDs (include parent roles)
     *
     * @return int[]
     */
    public function getAllUserRolesIDs(): array;

    /**
     * Returns user`s language name
     *
     * @return string|null
     */
    public function getLanguageName(): ?string;

    /**
     * @return NULL|\Model_Language
     */
    public function getLanguage(): ?\Model_Language;

    /**
     * Search for user by username or e-mail
     * @param $username_or_email
     * @throws \HTTP_Exception_403
     * @return UserInterface|null
     */
    public function searchBy($username_or_email): ?UserInterface;

    public function beforeSignIn();

    /**
     * @return void
     * @throws \Auth_Exception_WrongIP
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
     * Filters only active users
     *
     * @return $this
     * @deprecated
     */
    public function filter_active();

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
    public function getEmail(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setEmail(string $value): UserInterface;

    /**
     * Возвращает основной номер телефона
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
     * @return array
     */
    public function as_array(): array;

    /**
     * Forces authorization if user is not logged in
     *
     * @throws \HTTP_Exception_401
     * @return void
     */
    public function forceAuthorization(): void;
}
