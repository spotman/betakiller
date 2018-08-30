<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface NotificationGroupInterface extends ExtendedOrmInterface
{
    public const GROUP_CODENAME1 = 'groupCodename1';
    public const GROUP_CODENAME2 = 'groupCodename2';
    public const GROUP_CODENAME3 = 'groupCodename3';
    public const GROUP_CODENAME4 = 'groupCodename4';
    public const GROUP_CODENAME5 = 'groupCodename5';

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
     * @return \BetaKiller\Model\UserInterface
     */
    public function getUserOffRelated(): UserInterface;

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
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function enableForRole(RoleInterface $roleModel): NotificationGroupInterface;

    /**
     * @param \BetaKiller\Model\RoleInterface $roleModel
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function disableForRole(RoleInterface $roleModel): NotificationGroupInterface;
}
