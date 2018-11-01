<?php
declare(strict_types=1);

namespace BetaKiller\Task\Notification;

use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Log\Logger;
use BetaKiller\Model\NotificationGroup;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Repository\NotificationGroupRepository;
use BetaKiller\Repository\RoleRepository;
use BetaKiller\Task\AbstractTask;

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
     * @var \BetaKiller\Log\Logger
     */
    private $logger;

    /**
     * ChangePassword constructor.
     *
     * @param \BetaKiller\Repository\NotificationGroupRepository $groupRepo
     * @param \BetaKiller\Config\NotificationConfigInterface     $config
     * @param \BetaKiller\Repository\RoleRepository              $roleRepo
     * @param \BetaKiller\Log\Logger                             $logger
     */
    public function __construct(
        NotificationGroupRepository $groupRepo,
        NotificationConfigInterface $config,
        RoleRepository $roleRepo,
        Logger $logger
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
        foreach ($this->groupRepo->getAllEnabled() as $groupModel) {
            if (!\in_array($groupModel->getCodename(), $groupCodenames, true)) {
                // Disable unused group
                $groupModel->disable();
                $this->groupRepo->save($groupModel);

                $this->logger->info('Group ":group" was disabled', [
                    ':group' => $groupModel->getCodename(),
                ]);
            }
        }

        // Add new groups / re-enable existing
        foreach ($groupCodenames as $groupCodename) {
            $groupModel = $this->groupRepo->findByCodename($groupCodename);

            if (!$groupModel) {
                // Create new group
                $groupModel = (new NotificationGroup())
                    ->setCodename($groupCodename)
                    ->enable();

                $this->groupRepo->save($groupModel);

                $this->logger->info('Group ":group" created', [
                    ':group' => $groupCodename,
                ]);
            } elseif (!$groupModel->isEnabled()) {
                // Re-enable existing group
                $groupModel->enable();
                $this->groupRepo->save($groupModel);

                $this->logger->info('Group ":group" was re-enabled', [
                    ':group' => $groupModel->getCodename(),
                ]);
            }

            $this->importGroup($groupModel);
        }

        $this->logger->info('Groups successfully imported!');
    }

    /**
     * @param \BetaKiller\Model\NotificationGroupInterface $groupModel
     *
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function importGroup(NotificationGroupInterface $groupModel): void
    {
        $groupCodename = $groupModel->getCodename();

        // Get updated roles
        $rolesCodenames = $this->config->getGroupRoles($groupCodename);

        // Remove unused groups
        foreach ($groupModel->getRoles() as $currentRole) {
            if (!\in_array($currentRole->getName(), $rolesCodenames, true)) {
                // Unused group => remove it
                $groupModel->removeRole($currentRole);
                $this->logger->info('Role ":role" removed from group ":group"', [
                    ':role'  => $currentRole->getName(),
                    ':group' => $groupCodename,
                ]);
            }
        }

        // Add new roles
        foreach ($rolesCodenames as $roleCodename) {
            $roleModel = $this->roleRepo->getByName($roleCodename);

            if (!$groupModel->hasRole($roleModel)) {
                $groupModel->addRole($roleModel);
                $this->logger->info('Role ":role" added to group ":group"', [
                    ':role'  => $roleCodename,
                    ':group' => $groupCodename,
                ]);
            }
        }
    }
}
