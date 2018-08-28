<?php
namespace BetaKiller\Model;

interface NotificationGroupUserOffInterface
{
    /**
     * @return int
     */
    public function getGroupId(): int;

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\NotificationGroupUserOffInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function setGroupId(int $value): NotificationGroupUserOffInterface;

    /**
     * @return int
     */
    public function getUserId(): int;

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\NotificationGroupUserOffInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function setUserId(int $value): NotificationGroupUserOffInterface;

    /**
     * @return string
     */
    public function getGroupCodename(): string;
}
