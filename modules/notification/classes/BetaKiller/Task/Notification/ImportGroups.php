<?php
declare(strict_types=1);

namespace BetaKiller\Task\Notification;

use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Model\NotificationGroup;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Repository\NotificationGroupRepository;
use BetaKiller\Repository\RoleRepository;
use BetaKiller\Task\AbstractTask;
use Psr\Log\LoggerInterface;

class ImportGroups extends AbstractTask
{
    /**
     * @var \BetaKiller\Config\NotificationConfigInterface
     */
    private $config;

    /**
     * @var \BetaKiller\Repository\RoleRepository
     */
    private $roleRepo;

    /**
     * @var \BetaKiller\Repository\NotificationGroupRepository
     */
    private $groupRepo;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * ChangePassword constructor.
     *
     * @param \BetaKiller\Repository\NotificationGroupRepository $groupRepo
     * @param \BetaKiller\Config\NotificationConfigInterface     $config
     * @param \BetaKiller\Repository\RoleRepository              $roleRepo
     * @param \Psr\Log\LoggerInterface                           $logger
     */
    public function __construct(
        NotificationGroupRepository $groupRepo,
        NotificationConfigInterface $config,
        RoleRepository $roleRepo,
        LoggerInterface $logger
    ) {
        $this->groupRepo = $groupRepo;
        $this->config    = $config;
        $this->roleRepo  = $roleRepo;
        $this->logger    = $logger;

        parent::__construct();
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        $groupCodenames = $this->config->getGroups();

        // Disable unused groups
        foreach ($this->groupRepo->getAllEnabled() as $group) {
            if (!\in_array($group->getCodename(), $groupCodenames, true)) {
                // Disable unused group
                $group->disable();
                $this->groupRepo->save($group);

                $this->logger->info('Group ":group" was disabled', [
                    ':group' => $group->getCodename(),
                ]);
            }
        }

        // Add new groups / re-enable existing
        foreach ($groupCodenames as $groupCodename) {
            $group = $this->groupRepo->findByCodename($groupCodename);

            if (!$group) {
                // Create new group
                $group = (new NotificationGroup())
                    ->setCodename($groupCodename)
                    ->enable();

                $this->groupRepo->save($group);

                $this->logger->info('Group ":group" created', [
                    ':group' => $groupCodename,
                ]);
            } elseif (!$group->isEnabled()) {
                // Re-enable existing group
                $group->enable();
                $this->groupRepo->save($group);

                $this->logger->info('Group ":group" was re-enabled', [
                    ':group' => $group->getCodename(),
                ]);
            }

            $this->importGroup($group);
        }
    }

    /**
     * @param \BetaKiller\Model\NotificationGroupInterface $group
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function importGroup(NotificationGroupInterface $group): void
    {
        $groupCodename = $group->getCodename();

        if ($this->config->isSystemGroup($groupCodename)) {
            $group->markAsSystem();
        } else {
            $group->markAsRegular();
        }

        if ($this->config->isGroupFreqControlled($groupCodename)) {
            $group->enableFrequencyControl();
        } else {
            $group->disableFrequencyControl();
        }

        // Get updated roles
        $rolesCodenames = $this->config->getGroupRoles($groupCodename);

        // Remove unused roles
        foreach ($group->getRoles() as $currentRole) {
            if (!\in_array($currentRole->getName(), $rolesCodenames, true)) {
                // Unused roles => remove it
                $group->removeRole($currentRole);
                $this->logger->info('Role ":role" removed from group ":group"', [
                    ':role'  => $currentRole->getName(),
                    ':group' => $groupCodename,
                ]);
            }
        }

        // Add new roles
        foreach ($rolesCodenames as $roleCodename) {
            $role = $this->roleRepo->getByName($roleCodename);

            if (!$group->hasRole($role)) {
                $group->addRole($role);
                $this->logger->info('Role ":role" added to group ":group"', [
                    ':role'  => $roleCodename,
                    ':group' => $groupCodename,
                ]);
            }
        }

        $this->groupRepo->save($group);
    }
}
