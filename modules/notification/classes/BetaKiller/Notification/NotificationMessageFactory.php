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
     * @param string $name
     *
     * @return \BetaKiller\Notification\NotificationMessageInterface
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Notification\NotificationException
     */
    public function create(string $name): NotificationMessageInterface
    {
        $instance = new NotificationMessage($name);

        // Fetch group by message codename
        $groupCodename = $this->getGroupCodename($name);
        if (!$groupCodename) {
            throw new NotificationException(
                'Not found group codename by message codename ":messageCodename"', [
                    'messageCodename' => $name,
                ]
            );
        }

        // Fetch targets (users) by group
        $users = $this->groupRepository->getGroupUsers($groupCodename);

        // Add targets to message
        $instance->addTargetUsers($users);

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
