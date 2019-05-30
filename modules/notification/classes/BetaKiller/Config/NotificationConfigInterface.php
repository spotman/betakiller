<?php
declare(strict_types=1);

namespace BetaKiller\Config;

interface NotificationConfigInterface
{
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
     * @param string $groupCodename
     *
     * @return bool
     */
    public function isSystemGroup(string $groupCodename): bool;

    /**
     * @param string $groupCodename
     *
     * @return bool
     */
    public function isGroupFreqControlled(string $groupCodename): bool;

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
