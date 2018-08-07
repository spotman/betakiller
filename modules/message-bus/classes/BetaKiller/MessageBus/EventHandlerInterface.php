<?php
namespace BetaKiller\MessageBus;

interface EventHandlerInterface extends MessageHandlerInterface
{
    /**
     * @param \BetaKiller\MessageBus\EventMessageInterface $message
     */
    public function handleEvent($message): void;
}
