<?php
declare(strict_types=1);

namespace BetaKiller\Repository;

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
     * @param \BetaKiller\Model\RoleInterface[] $roles
     *
     * @param bool|null                         $includeSystem
     *
     * @return \BetaKiller\Model\NotificationGroupInterface[]
     */
    public function getRolesGroups(array $roles, bool $includeSystem = null): array;
}
