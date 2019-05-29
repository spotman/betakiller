<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationGroupUserConfigInterface;
use BetaKiller\Model\UserInterface;

/**
 * Interface NotificationGroupUserConfigRepositoryInterface
 *
 * @package BetaKiller\Repository
 */
interface NotificationGroupUserConfigRepositoryInterface extends RepositoryInterface
{
    /**
     *
     * @param \BetaKiller\Model\NotificationGroupInterface $group
     *
     * @param \BetaKiller\Model\UserInterface              $user
     *
     * @return \BetaKiller\Model\NotificationGroupUserConfigInterface|null
     */
    public function findByUserAndGroup(
        NotificationGroupInterface $group,
        UserInterface $user
    ): ?NotificationGroupUserConfigInterface;
}
