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
        // TODO Fetch targets (users) by group
        // TODO Add targets to message via NotificationMessageInterface::addTargetUsers() method

        return $instance;
    }
}
