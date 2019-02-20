<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface NotificationGroupInterface extends DispatchableEntityInterface, HasI18nKeyNameInterface
{
    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function enable(): NotificationGroupInterface;

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function disable(): NotificationGroupInterface;

    /**
     * @return string
     */
    public function getCodename(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function setCodename(string $value): NotificationGroupInterface;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function setDescription(string $value): NotificationGroupInterface;

    /**
     * Returns true if current group is allowed for provided user (complex check with roles intersection)
     *
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     */
    public function isAllowedToUser(UserInterface $user): bool;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return bool
     */
    public function isEnabledForUser(UserInterface $user): bool;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function enableForUser(UserInterface $user): NotificationGroupInterface;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function disableForUser(UserInterface $user): NotificationGroupInterface;

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return bool
     */
    public function hasRole(RoleInterface $role): bool;

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function addRole(RoleInterface $role): NotificationGroupInterface;

    /**
     * @param \BetaKiller\Model\RoleInterface $role
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function removeRole(RoleInterface $role): NotificationGroupInterface;

    /**
     * @return \BetaKiller\Model\RoleInterface[]
     */
    public function getRoles(): array;

    /**
     * @return \BetaKiller\Model\UserInterface[]
     */
    public function getDisabledUsers(): array;

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function markAsSystem(): NotificationGroupInterface;

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function markAsRegular(): NotificationGroupInterface;

    /**
     * @return bool
     */
    public function isSystem(): bool;
}
