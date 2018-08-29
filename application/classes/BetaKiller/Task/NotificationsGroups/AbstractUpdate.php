<?php
declare(strict_types=1);

namespace BetaKiller\Task\NotificationsGroups;

use BetaKiller\Config\NotificationConfig;
use BetaKiller\Model\NotificationGroup;
use BetaKiller\Model\NotificationGroupRole;
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
     * @return array
     */
    protected function getGroupsFromConfig(): array
    {
        return $this->notificationConfig->getGroups();
    }

    /**
     * @param string $groupCodename
     *
     * @return bool
     */
    protected function hasGroupCodenameInConfig(string $groupCodename): bool
    {
        return \in_array($groupCodename, $this->getGroupsFromConfig(), true);
    }

    /**
     * @param string $groupCodename
     *
     * @return array
     */
    protected function getGroupRolesFromConfig(string $groupCodename): array
    {
        return $this->notificationConfig->getGroupRoles($groupCodename);
    }

    /**
     * @param string $groupCodename
     *
     * @return int
     */
    protected function createGroup(string $groupCodename): int
    {
        return (new NotificationGroup())
            ->setCodename($groupCodename)
            ->save()
            ->get_id();
    }

    /**
     * @param string $roleCodename
     *
     * @return int
     */
    protected function getRoleId(string $roleCodename): int
    {
        return (int)$this->roleRepository->getByName($roleCodename)->get_id();
    }

    /**
     * @param int $groupId
     * @param int $roleId
     *
     * @return \BetaKiller\Task\NotificationsGroups\AbstractUpdate
     */
    protected function addGroupRole(int $groupId, int $roleId): AbstractUpdate
    {
        (new NotificationGroupRole())
            ->setGroupId($groupId)
            ->setRoleId($roleId)
            ->save();

        return $this;
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
     * @param string $groupCodename
     *
     * @return \BetaKiller\Task\NotificationsGroups\AbstractUpdate
     */
    protected function deleteGroup(string $groupCodename): AbstractUpdate
    {
        $groupModel = $this
            ->notificationGroupRepository
            ->getByCodename($groupCodename);

        if ($groupModel) {
            $groupModel->delete();
        }

        return $this;
    }
}
