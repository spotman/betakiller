<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface NotificationGroupUserOffInterface extends ExtendedOrmInterface
{
    /**
     * @return int
     */
    public function getGroupId(): int;

    /**
     * @param int $value
     *
     * @return \BetaKiller\Model\NotificationGroupUserOffInterface
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
     */
    public function setUserId(int $value): NotificationGroupUserOffInterface;
}
