<?php
declare(strict_types=1);

namespace BetaKiller\Config;

interface NotificationConfigInterface
{
    public const CONFIG_GROUP_NAME  = 'notifications';
    public const PATH_GROUPS        = ['groups'];
    public const PATH_GROUP_ROLES   = ['groups', 'groupCodename' => ''];
    public const PATH_MESSAGES      = ['messages'];
    public const PATH_MESSAGE_GROUP = ['messages', 'messageCodename' => '', 'group'];

    /**
     * @return string[] ['groupCodename1','groupCodename1',..]
     */
    public function getGroups(): array;

    /**
     * @param string $groupCodename
     *
     * @return string[] ['roleCodename1','roleCodename2',..]
     */
    public function getGroupRoles(string $groupCodename): array;

    /**
     * @param string $messageCodename
     *
     * @return string
     */
    public function getMessageGroup(string $messageCodename): string;

    /**
     * @param string $groupCodename
     *
     * @return string[]
     */
    public function getGroupMessages(string $groupCodename): array;
}
