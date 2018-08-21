<?php
namespace BetaKiller\Model;

interface NotificationGroupRoleInterface
{
    public function rules();

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
     * @return array[string self::TABLE_FIELD_GROUP_ID, string self::TABLE_FIELD_ROLE_ID]
     */
    public function getAll(): array;

    /**
     * @return string
     */
    public function getGroupCodename(): string;

    /**
     * @return string
     */
    public function getRoleCodename(): string;
}
