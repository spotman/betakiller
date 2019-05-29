<?php
namespace BetaKiller\Notification;

class MessageFactory
{
    /**
     * @param string $messageCodename
     *
     * @return \BetaKiller\Notification\MessageInterface
     */
    public function create(string $messageCodename): MessageInterface
    {
        return new Message($messageCodename);
    }
}
