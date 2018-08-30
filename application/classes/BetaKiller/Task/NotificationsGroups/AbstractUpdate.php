<?php
declare(strict_types=1);

namespace BetaKiller\Task\NotificationsGroups;

use BetaKiller\Config\NotificationConfig;
use BetaKiller\Model\NotificationGroup;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Repository\NotificationGroupRepository;
use BetaKiller\Repository\RoleRepository;
use BetaKiller\Task\AbstractTask;

abstract class AbstractUpdate extends AbstractTask
{
    /**
     * @var \BetaKiller\Config\NotificationConfig
     */
    private $notificationConfig;

    /**
     * @var \BetaKiller\Repository\RoleRepository
     */
    private $roleRepository;

    /**
     * @var \BetaKiller\Repository\NotificationGroupRepository
     */
    private $notificationGroupRepository;

    /**
     * ChangePassword constructor.
     *
     * @param \BetaKiller\Repository\NotificationGroupRepository $notificationGroupRepository
     * @param \BetaKiller\Config\NotificationConfig              $notificationConfig
     * @param \BetaKiller\Repository\RoleRepository              $roleRepository
     */
    public function __construct(
        NotificationGroupRepository $notificationGroupRepository,
        NotificationConfig $notificationConfig,
        RoleRepository $roleRepository
    ) {
        $this->notificationGroupRepository = $notificationGroupRepository;
        $this->notificationConfig          = $notificationConfig;
        $this->roleRepository              = $roleRepository;

        parent::__construct();
    }

    /**
     * @param string $groupCodename
     *
     * @return bool
     */
    protected function hasGroupCodenameInConfig(string $groupCodename): bool
    {
        return \in_array($groupCodename, $this->getGroupsCodenamesFromConfig(), true);
    }

    /**
     * @return array
     */
    protected function getGroupsCodenamesFromConfig(): array
    {
        $codenames = $this->notificationConfig->getGroups();
        $models    = [];
        foreach ($codenames as $codename) {
            $models[] = $codename;
        }

        return $models;
    }

    /**
     * @param string $groupCodename
     *
     * @return array
     */
    protected function getGroupRolesCodenamesFromConfig(string $groupCodename): array
    {
        $codenames = $this->notificationConfig->getGroupRoles($groupCodename);
        $models    = [];
        foreach ($codenames as $codename) {
            $models[] = $codename;
        }

        return $models;
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    protected function createGroupModel(string $codename): NotificationGroupInterface
    {
        return (new NotificationGroup())->setCodename($codename);
    }

    /**
     * @param string $groupCodename
     *
     * @return \BetaKiller\Model\NotificationGroupInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function findGroup(string $groupCodename): ?NotificationGroupInterface
    {
        return $this->notificationGroupRepository->findGroup($groupCodename);
    }

    /**
     * @return \BetaKiller\Task\NotificationsGroups\AbstractUpdate
     */
    protected function deleteGroups(): AbstractUpdate
    {
        (new NotificationGroup())->delete_all();

        return $this;
    }

    /**
     * @param string $codename
     *
     * @return \BetaKiller\Model\RoleInterface
     */
    protected function findRole(string $codename): RoleInterface
    {
        return $this->roleRepository->getByName($codename);
    }
}
