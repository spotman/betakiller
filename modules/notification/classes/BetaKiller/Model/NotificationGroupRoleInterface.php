<?php
namespace BetaKiller\Model;

interface NotificationGroupRoleInterface
{
    /**
     * @return int
     */
    public function getGroupId(): int;

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\NotificationGroupRoleInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function setGroupId(int $value): NotificationGroupRoleInterface;

    /**
     * @return int
     */
    public function getRoleId(): int;

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\NotificationGroupRoleInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function setRoleId(int $value): NotificationGroupRoleInterface;

    /**
     * @return string
     */
    public function getGroupCodename(): string;

    /**
     * @return string
     */
    public function getRoleCodename(): string;
}
