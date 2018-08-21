<?php
namespace BetaKiller\Model;

interface NotificationGroupInterface
{
    /**
     * @return array[string self::TABLE_FIELD_CODENAME, string self::TABLE_FIELD_DESCRIPTION]
     */
    public function getAll(): array;

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
