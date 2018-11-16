<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface NotificationGroupInterface extends DispatchableEntityInterface
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
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return bool
     */
    public function isAllowedToUser(UserInterface $userModel): bool;

    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return bool
     */
    public function isEnabledForUser(UserInterface $userModel): bool;

    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function enableForUser(UserInterface $userModel): NotificationGroupInterface;

    /**
     * @param \BetaKiller\Model\UserInterface $userModel
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function disableForUser(UserInterface $userModel): NotificationGroupInterface;

    /**
     * @param \BetaKiller\Model\RoleInterface $roleModel
     *
     * @return bool
     */
    public function hasRole(RoleInterface $roleModel): bool;

    /**
     * @param \BetaKiller\Model\RoleInterface $roleModel
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function addRole(RoleInterface $roleModel): NotificationGroupInterface;

    /**
     * @param \BetaKiller\Model\RoleInterface $roleModel
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function removeRole(RoleInterface $roleModel): NotificationGroupInterface;

    /**
     * @return \BetaKiller\Model\RoleInterface[]
     */
    public function getRoles(): array;

    /**
     * @return \BetaKiller\Model\UserInterface[]
     */
    public function getDisabledUsers(): array;
}
