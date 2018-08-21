<?php
namespace BetaKiller\Model;

interface NotificationGroupUserInterface
{
    public function rules();

    /**
     * @return int
     */
    public function getGroupId(): int;

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\NotificationGroupUserInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function setGroupId(int $value): NotificationGroupUserInterface;

    /**
     * @return int
     */
    public function getUserId(): int;

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\NotificationGroupUserInterface
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function setUserId(int $value): NotificationGroupUserInterface;

    /**
     * @return array[string self::TABLE_FIELD_GROUP_ID, string self::TABLE_FIELD_USER_ID]
     */
    public function getAll(): array;

    /**
     * @return string
     */
    public function getGroupCodename(): string;
}
