<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface NotificationGroupInterface
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
}
