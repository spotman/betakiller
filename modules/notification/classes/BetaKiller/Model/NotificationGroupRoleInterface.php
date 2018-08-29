<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface NotificationGroupRoleInterface extends ExtendedOrmInterface
{
    /**
     * @return int
     */
    public function getGroupId(): int;

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\NotificationGroupRoleInterface
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
     */
    public function setRoleId(int $value): NotificationGroupRoleInterface;
}
