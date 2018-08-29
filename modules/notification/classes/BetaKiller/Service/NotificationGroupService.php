<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Model\NotificationGroupInterface;
use BetaKiller\Model\NotificationGroupUserOff;
use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\NotificationGroupRepository;
use BetaKiller\Repository\NotificationGroupUserOffRepository;

class NotificationGroupService
{
    /**
     * @var \BetaKiller\Repository\NotificationGroupRepository
     */
    private $groupRepository;

    /**
     * @var \BetaKiller\Model\User
     */
    private $user;

    /**
     * @var \BetaKiller\Repository\NotificationGroupUserOffRepository
     */
    private $groupUserOffRepository;

    /**
     * @param \BetaKiller\Repository\NotificationGroupRepository        $notificationGroupRepository
     * @param \BetaKiller\Repository\NotificationGroupUserOffRepository $notificationGroupUserOffRepository
     * @param \BetaKiller\Model\UserInterface                           $user
     */
    public function __construct(
        NotificationGroupRepository $notificationGroupRepository,
        NotificationGroupUserOffRepository $notificationGroupUserOffRepository,
        UserInterface $user
    ) {
        $this->groupRepository        = $notificationGroupRepository;
        $this->user                   = $user;
        $this->groupUserOffRepository = $notificationGroupUserOffRepository;
    }

    /**
     * @param string $groupCodename
     * @param int    $userId
     *
     * @return \BetaKiller\Service\NotificationGroupService
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function addUserToGroup(string $groupCodename, int $userId): NotificationGroupService
    {
        $groupUserOff = $this->getGroupUserOff($groupCodename, $userId);
        if ($groupUserOff->get_id()) {
            $groupUserOff->delete();
        }

        return $this;
    }

    /**
     * @param string $groupCodename
     * @param int    $userId
     *
     * @return \BetaKiller\Service\NotificationGroupService
     */
    public function removeUserFromGroup(string $groupCodename, int $userId): NotificationGroupService
    {
        $groupModel = $this->getGroup($groupCodename);
        if ($groupModel) {
            // todo or update on duplicate?
            $groupUserOff = $this->getGroupUserOff($groupCodename, $userId);
            if (!$groupUserOff->get_id()) {
                (new NotificationGroupUserOff())
                    ->setGroupId((int)$groupModel->get_id())
                    ->setUserId($userId)
                    ->save();
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getUserGroups(): array
    {
        return $this
            ->groupRepository
            ->getRolesGroups($this->getUserRolesIds());
    }

    /**
     * @return array
     */
    public function getUserGroupsActive(): array
    {
        return $this
            ->groupRepository
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

    /**
     * @param string $groupCodename
     *
     * @return \BetaKiller\Model\NotificationGroupInterface|null
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function getGroup(string $groupCodename): ?NotificationGroupInterface
    {
        return $this->groupRepository->getByCodename($groupCodename);
    }

    /**
     * @param string $groupCodename
     * @param int    $userId
     *
     * @return \BetaKiller\Model\NotificationGroupUserOff
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function getGroupUserOff(string $groupCodename, int $userId): NotificationGroupUserOff
    {
        return $this->groupUserOffRepository->get($groupCodename, $userId);
    }
}
