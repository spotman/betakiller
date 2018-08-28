<?php
namespace BetaKiller\Notification;

class NotificationMessageFactory
{
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
//        $groupCodename = $this->notificationConfig->getMessageGroup($messageCodename);
//        var_dump($groupCodename);
//        exit;
    }
}
