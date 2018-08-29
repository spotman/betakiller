<?php
declare(strict_types=1);

namespace BetaKiller\Task\NotificationsGroups;

use BetaKiller\Config\NotificationConfig;
use BetaKiller\Model\NotificationGroup;
use BetaKiller\Model\NotificationGroupRole;
use BetaKiller\Repository\NotificationGroupRepository;
use BetaKiller\Repository\RoleRepository;
use BetaKiller\Task\AbstractTask;

class UpdateAll extends AbstractTask
{
    /**
     * @var \BetaKiller\Config\NotificationConfig
     */
    private $notificationConfig;

    /**
     * @var \BetaKiller\Repository\NotificationGroupRepository
     */
    private $groupRepository;

    /**
     * @var \BetaKiller\Repository\RoleRepository
     */
    private $roleRepository;

    /**
     * ChangePassword constructor.
     *
     * @param \BetaKiller\Config\NotificationConfig              $notificationConfig
     * @param \BetaKiller\Repository\NotificationGroupRepository $groupRepository
     * @param \BetaKiller\Repository\RoleRepository              $roleRepository
     */
    public function __construct(
        NotificationConfig $notificationConfig,
        NotificationGroupRepository $groupRepository,
        RoleRepository $roleRepository
    ) {
        $this->notificationConfig = $notificationConfig;

        parent::__construct();
        $this->groupRepository = $groupRepository;
        $this->roleRepository  = $roleRepository;
    }

    public function run(): void
    {
        $continue = $this->read(
            'Delete all groups and create new groups from settings? [yes/no]'
        );
        $continue = strtolower($continue);
        while (!\in_array($continue, ['yes', 'no'])) {
            $continue = $this->read('Type: yes/no');
            $continue = strtolower($continue);
        }
        if ($continue === 'no') {
            return;
        }

        $this->deleteGroups();

        foreach ($this->getGroupsFromConfig() as $groupCodename) {
            $groupId        = $this->createGroup($groupCodename);
            $rolesCodenames = $this->getGroupRolesFromConfig($groupCodename);
            foreach ($rolesCodenames as $roleCodename) {
                $roleId = $this->getRoleId($roleCodename);
                $this->addGroupRole($groupId, $roleId);
            }
        }

        $this->write('Groups successfully updated!', self::COLOR_GREEN);
    }

    /**
     * @return array
     */
    private function getGroupsFromConfig(): array
    {
        return $this->notificationConfig->getGroups();
    }

    /**
     * @param string $groupCodename
     *
     * @return array
     */
    private function getGroupRolesFromConfig(string $groupCodename): array
    {
        return $this->notificationConfig->getGroupRoles($groupCodename);
    }

    /**
     * @param string $groupCodename
     *
     * @return int
     */
    private function createGroup(string $groupCodename): int
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
    private function getRoleId(string $roleCodename): int
    {
        return (int)$this->roleRepository->getByName($roleCodename)->get_id();
    }

    /**
     * @param int $groupId
     * @param int $roleId
     *
     * @return \BetaKiller\Task\NotificationsGroups\Update
     */
    private function addGroupRole(int $groupId, int $roleId): Update
    {
        (new NotificationGroupRole())
            ->setGroupId($groupId)
            ->setRoleId($roleId)
            ->save();

        return $this;
    }

    /**
     * @return \BetaKiller\Task\NotificationsGroups\Update
     */
    private function deleteGroups(): Update
    {
        (new NotificationGroup())->delete_all();

        return $this;
    }
}
