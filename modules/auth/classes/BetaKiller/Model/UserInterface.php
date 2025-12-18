<?php

declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\MessageBus\RestrictionTargetInterface;
use BetaKiller\Notification\EmailMessageTargetInterface;
use BetaKiller\Notification\OnlineMessageTargetInterface;
use BetaKiller\Notification\PhoneMessageTargetInterface;
use BetaKiller\Url\RequestUserInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;
use DateTimeImmutable;
use Spotman\Acl\AclUserInterface;

/**
 * Interface UserInterface
 *
 * @package BetaKiller\Model
 */
interface UserInterface extends DispatchableEntityInterface, OrmInterface, HasWorkflowStateInterface,
    EmailMessageTargetInterface, PhoneMessageTargetInterface, OnlineMessageTargetInterface, AclUserInterface,
    EntityWithAclSpecInterface, RequestUserInterface, CreatedAtInterface, RestrictionTargetInterface
{
    public static function isAutoApproveEnabled(): bool;

    public static function isIpAddressEnabled(): bool;

    public static function isIpAddressRequired(): bool;

    public static function isEmailEnabled(): bool;

    public static function isEmailRequired(): bool;

    public static function isEmailUniqueEnabled(): bool;

    public static function isEmailRegexEnabled(): bool;

    public static function isPhoneEnabled(): bool;

    public static function isPhoneRequired(): bool;

    public static function isPhoneUniqueEnabled(): bool;

    public static function isUsernameEnabled(): bool;

    public static function isUsernameRequired(): bool;

    public static function isUsernameUniqueEnabled(): bool;

    public static function isPasswordEnabled(): bool;

    public static function isPasswordRequired(): bool;

    public static function isPasswordUniqueEnabled(): bool;

    public static function isFirstNameEnabled(): bool;

    public static function isFirstNameRequired(): bool;

    public static function isLastNameEnabled(): bool;

    public static function isLastNameRequired(): bool;

    public function isMinion(): bool;

    public function isAdmin(): bool;

    /**
     * @return \DateTimeImmutable|null
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
    public function inStateCreated(): bool;

    /**
     * @return bool
     */
    public function inStatePending(): bool;

    /**
     * @return bool
     */
    public function inStateApproved(): bool;

    /**
     * @return bool
     */
    public function inStateBanned(): bool;

    /**
     * @return bool
     */
    public function inStateSuspended(): bool;

    /**
     * @return bool
     */
    public function inStateRemoved(): bool;

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
     * Get user`s roles objects (direct only, exclude parent roles)
     *
     * @return \BetaKiller\Model\RoleInterface[]
     */
    public function getRoles(): array;

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
     * @param \BetaKiller\Model\Phone $number
     *
     * @return \BetaKiller\Model\UserInterface
     */
    public function setPhone(Phone $number): UserInterface;

    /**
     * Returns primary phone number
     *
     * @return \BetaKiller\Model\Phone
     */
    public function getPhone(): Phone;

    /**
     * @return bool
     */
    public function hasPhoneDefined(): bool;

    /**
     * Mark email as verified
     */
    public function markEmailAsVerified(): void;

    /**
     * Mark email as non-verified
     */
    public function markEmailAsUnverified(): void;

    /**
     * Returns true if email is verified
     */
    public function isEmailVerified(): bool;

    /**
     * Mark phone number as verified
     */
    public function markPhoneAsVerified(): void;

    /**
     * Mark phone number as non-verified
     */
    public function markPhoneAsUnverified(): void;

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

    public function setApprovedAt(DateTimeImmutable $date = null): UserInterface;

    public function hasApprovedAt(): bool;

    public function getApprovedAt(): DateTimeImmutable;
}
