<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

use BetaKiller\Model\NotificationFrequencyInterface;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\UserInterface;

interface NotificationGroupRepositoryInterface extends DispatchableRepositoryInterface
{
    /**
     * @param string $codename
     *
     * @return \BetaKiller\Model\NotificationGroupInterface|null
     */
    public function findByCodename(string $codename): ?NotificationGroupInterface;

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    public function getByCodename(string $codename): NotificationGroupInterface;

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface[]
     */
    public function getAllEnabled(): array;

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface[]
     */
    public function getAllDisabled(): array;

    /**
     * @return NotificationGroupInterface[]
     */
    public function getScheduledGroups(): array;

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @param bool|null                       $includeSystem
     *
     * @return \BetaKiller\Model\NotificationGroupInterface[]
     */
    public function getUserGroups(UserInterface $user, bool $includeSystem = null): array;

    /**
     * @param \BetaKiller\Model\NotificationGroupInterface $groupModel
     *
     * @param \BetaKiller\Model\NotificationFrequencyInterface|null $freq
     *
     * @return \BetaKiller\Model\UserInterface[]
     */
    public function findGroupUsers(
        NotificationGroupInterface $groupModel,
        NotificationFrequencyInterface $freq = null
    ): array;
}
