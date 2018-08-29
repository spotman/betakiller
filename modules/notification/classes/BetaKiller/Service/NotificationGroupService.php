<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\NotificationGroupRepository;

class NotificationGroupService
{
    /**
     * @var \BetaKiller\Repository\NotificationGroupRepository
     */
    private $notificationGroupRepository;

    /**
     * @var \BetaKiller\Model\User
     */
    private $user;

    /**
     * NotificationGroupService constructor.
     *
     * @param \BetaKiller\Repository\NotificationGroupRepository $notificationGroupRepository
     * @param \BetaKiller\Model\UserInterface                    $user
     */
    public function __construct(
        NotificationGroupRepository $notificationGroupRepository,
        UserInterface $user
    ) {
        $this->notificationGroupRepository = $notificationGroupRepository;
        $this->user                        = $user;
    }

    /**
     * @param string $groupCodename
     *
     * @return \BetaKiller\Service\NotificationGroupService
     */
    public function addUserToGroup(string $groupCodename): NotificationGroupService
    {
        // TODO progress
        return $this;
    }

    /**
     * @param string $groupCodename
     *
     * @return \BetaKiller\Service\NotificationGroupService
     */
    public function removeUserFromGroup(string $groupCodename): NotificationGroupService
    {
        // TODO progress
        return $this;
    }

    /**
     * @return array
     */
    public function getUserGroups(): array
    {
        return $this
            ->notificationGroupRepository
            ->getRolesGroups($this->getUserRolesIds());
    }

    /**
     * @return array
     */
    public function getUserGroupsActive(): array
    {
        return $this
            ->notificationGroupRepository
            ->getRolesGroupsActive(
                $this->getUserRolesIds(),
                (int)$this->user->get_id()
            );
    }

    /**
     * @return array
     */
    private function getUserRolesIds(): array
    {
        $rolesIds = [];
        foreach ($this->user->getAccessControlRoles() as $aclRole) {
            $rolesIds[] = $aclRole->get_id();
        }

        return $rolesIds;
    }
}
