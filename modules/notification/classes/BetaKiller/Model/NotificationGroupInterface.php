<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface NotificationGroupInterface
{
    /**
     * Constants of codenames of groups
     */
    //public const GROUP_CODENAME1 = 'groupCodename1';

    /**
     * @return bool
     */
    public function getIsEnabled(): bool;

    /**
     * @param bool $state
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function setIsEnabled(bool $state): NotificationGroupInterface;

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
    public function isEnabledForRole(RoleInterface $roleModel): bool;

    /**
     * @param \BetaKiller\Model\RoleInterface $roleModel
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function enableForRole(RoleInterface $roleModel): NotificationGroupInterface;

    /**
     * @param \BetaKiller\Model\RoleInterface $roleModel
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function disableForRole(RoleInterface $roleModel): NotificationGroupInterface;

    /**
     * @return \BetaKiller\Model\RoleInterface[]
     */
    public function getRoles(): array;
}
