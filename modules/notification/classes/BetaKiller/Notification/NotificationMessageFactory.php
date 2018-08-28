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
     * @param string|null $name
     *
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function create(string $name): NotificationMessageInterface
    {
        $instance = new NotificationMessage($name);

        // TODO Fetch group by message codename
        $groupCodename = $this->getGroupCodename($name);
        if (!$groupCodename) {
            throw new NotificationException(
                'Not found group codename by message code name :messageCodename', [
                    'messageCodename' => $name,
                ]
            );
        }

        // TODO Fetch targets (users) by group
        $users = $this->groupRepository->getGroupUsers($groupCodename);
//        foreach ($users as $user) {
//            var_dump($user);
//        }
        var_dump($users);
        exit;

        // TODO Add targets to message via NotificationMessageInterface::addTargetUsers() method

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
