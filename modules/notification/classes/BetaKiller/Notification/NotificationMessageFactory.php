<?php
namespace BetaKiller\Notification;

use BetaKiller\Config\NotificationConfig;
use BetaKiller\Repository\NotificationGroupRepository;

class NotificationMessageFactory
{
    /**
     * @var \BetaKiller\Config\NotificationConfigInterface
     */
    private $notificationConfig;

    /**
     * @var \BetaKiller\Repository\NotificationGroupRepository
     */
    private $groupRepository;

    /**
     * NotificationMessageFactory constructor.
     *
     * @param \BetaKiller\Config\NotificationConfig              $notificationConfig
     * @param \BetaKiller\Repository\NotificationGroupRepository $groupRepository
     */
    public function __construct(
        NotificationConfig $notificationConfig,
        NotificationGroupRepository $groupRepository
    ) {
        $this->notificationConfig = $notificationConfig;
        $this->groupRepository    = $groupRepository;
    }

    /**
     * @param string $messageCodename
     *
     * @return \BetaKiller\Notification\NotificationMessageInterface
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function create(string $messageCodename): NotificationMessageInterface
    {
        $instance = new NotificationMessage($messageCodename);

        // Fetch group by message codename
        $groupCodename = $this->getGroupCodename($messageCodename);
        if (!$groupCodename) {
            throw new NotificationException(
                'Not found group codename by message codename ":messageCodename"', [
                    'messageCodename' => $messageCodename,
                ]
            );
        }

        $groupModel = $this->groupRepository->findByCodename($groupCodename);
        if (!$groupModel) {
            throw new NotificationException(
                'Not found group by group codename ":groupCodename"', [
                    'groupCodename' => $groupCodename,
                ]
            );
        }


        // Fetch targets (users) by group
        $usersModels = $this->groupRepository->findGroupUsers($groupModel);

        // Add targets to message
        $instance->addTargetUsers($usersModels);

        return $instance;
    }

    /**
     * @param string $messageCodename
     *
     * @return string
     */
    protected function getGroupCodename(string $messageCodename): string
    {
        return $this->notificationConfig->getMessageGroup($messageCodename);
    }
}
