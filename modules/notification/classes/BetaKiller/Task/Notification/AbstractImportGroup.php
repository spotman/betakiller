<?php
declare(strict_types=1);

namespace BetaKiller\Task\Notification;

use BetaKiller\Config\NotificationConfigInterface;
use BetaKiller\Log\Logger;
use BetaKiller\Model\NotificationGroup;
use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Repository\NotificationGroupRepository;
use BetaKiller\Repository\RoleRepository;
use BetaKiller\Task\AbstractTask;

abstract class AbstractImportGroup extends AbstractTask
{
    /**
     * @var \BetaKiller\Config\NotificationConfigInterface
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
     * @var \BetaKiller\Log\Logger
     */
    private $logger;

    /**
     * ChangePassword constructor.
     *
     * @param \BetaKiller\Repository\NotificationGroupRepository $notificationGroupRepository
     * @param \BetaKiller\Config\NotificationConfigInterface              $notificationConfig
     * @param \BetaKiller\Repository\RoleRepository              $roleRepository
     * @param \BetaKiller\Log\Logger                             $logger
     */
    public function __construct(
        NotificationGroupRepository $notificationGroupRepository,
        NotificationConfigInterface $notificationConfig,
        RoleRepository $roleRepository,
        Logger $logger
    ) {
        $this->notificationGroupRepository = $notificationGroupRepository;
        $this->notificationConfig          = $notificationConfig;
        $this->roleRepository              = $roleRepository;
        $this->logger                      = $logger;

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
     * @return string[] ['groupCodename1','groupCodename1',..]
     */
    protected function getGroupsCodenamesFromConfig(): array
    {
        return $this->notificationConfig->getGroups();
    }

    /**
     * @param string $groupCodename
     *
     * @return string[] ['roleCodename1','roleCodename2',..]
     */
    protected function getGroupRolesCodenamesFromConfig(string $groupCodename): array
    {
        return $this->notificationConfig->getGroupRoles($groupCodename);
    }

    /**
     * @param string $groupCodename
     *
     * @return \BetaKiller\Model\NotificationGroupInterface
     */
    protected function createGroup(string $groupCodename): NotificationGroupInterface
    {
        $groupModel = (new NotificationGroup())->setCodename($groupCodename);
        $this->notificationGroupRepository->save($groupModel);

        return $groupModel;
    }

    /**
     * @param string $groupCodename
     *
     * @return \BetaKiller\Model\NotificationGroupInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function findGroup(string $groupCodename): ?NotificationGroupInterface
    {
        return $this->notificationGroupRepository->findByCodename($groupCodename);
    }

    /**
     * @param \BetaKiller\Model\NotificationGroupInterface $groupModel
     *
     * @return \BetaKiller\Task\Notification\AbstractImportGroup
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function disableGroup(NotificationGroupInterface $groupModel): AbstractImportGroup
    {
        $groupModel->disable();
        $this->notificationGroupRepository->save($groupModel);

        return $this;
    }

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface[]
     * @throws \BetaKiller\Factory\FactoryException
     */
    protected function findGroupsEnabled(): array
    {
        return $this->notificationGroupRepository->getAllEnabled();
    }

    /**
     * @return \BetaKiller\Model\NotificationGroupInterface[]
     * @throws \BetaKiller\Repository\RepositoryException
     */
    protected function findGroupRoles(): array
    {
        return $this->notificationGroupRepository->getAll();
    }

    /**
     * @param string $roleCodename
     *
     * @return \BetaKiller\Model\RoleInterface
     */
    protected function findRole(string $roleCodename): RoleInterface
    {
        return $this->roleRepository->getByName($roleCodename);
    }

    /**
     * @return \BetaKiller\Log\Logger
     */
    protected function getLogger(): Logger
    {
        return $this->logger;
    }
}
