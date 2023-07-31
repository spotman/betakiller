<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Notification\MessageTargetInterface;
use BetaKiller\Url\RequestUserInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use BetaKiller\Workflow\HasWorkflowStateInterface;
use DateTimeImmutable;
use Spotman\Acl\AclUserInterface;

/**
 * Interface UserInterface
 *
 * @package BetaKiller\Model
 * @method UserStateInterface getWorkflowState()
 */
interface UserInterface extends DispatchableEntityInterface, OrmInterface, HasWorkflowStateInterface,
    MessageTargetInterface, AclUserInterface, EntityWithAclSpecInterface, RequestUserInterface
{
    /**
     * @return \DateTimeImmutable
     */
    public function getLastLoggedIn(): ?DateTimeImmutable;

    public function completeLogin(): void;

    /**
     * User claimed about registration
     */
    public function markAsRegistrationClaimed(): void;

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
     * @return bool
     */
    public function isRegistrationClaimed(): bool;

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
     * @return bool
     */
    public function hasUsername(): bool;

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
     * Returns true if current user is developer
     *
     * @return bool
     */
    public function isDeveloper(): bool;

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function addRole(RoleInterface $role): UserInterface;

    /**
     * Get all user`s roles objects (direct only, exclude parent roles)
     *
     * @return \BetaKiller\Model\RoleInterface[]
     */
    public function getRoles(): array;

    /**
     * Get all user`s roles objects (include parent roles)
     * BEWARE: CPU/DB hungry operation
     *
     * @return \BetaKiller\Model\RoleInterface[]
     */
    public function getAllRoles(): array;

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
    public function hasPhoneDefined(): bool;

    /**
     * Mark phone number as verified
     */
    public function markPhoneAsVerified(): void;

    /**
     * Returns true if phone is verified
     *
     * @return bool
     */
    public function isPhoneVerified(): bool;

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
     * @return void
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
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
