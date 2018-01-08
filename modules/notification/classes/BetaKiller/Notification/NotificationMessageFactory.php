<?php
namespace BetaKiller\Notification;

class NotificationMessageFactory
{
    /**
     * @param string|null $name
     *
     * @return \BetaKiller\Notification\NotificationMessageInterface
     */
    public function create(string $name = null): NotificationMessageInterface
    {
        $instance = new NotificationMessage;

        if ($name) {
            $instance->setTemplateName($name);
        }

        return $instance;
    }
}
