<?php
namespace BetaKiller\MessageBus;

interface EventHandlerInterface extends MessageHandlerInterface
{
    /**
     * @param \BetaKiller\MessageBus\EventMessageInterface $message
     * @param \BetaKiller\MessageBus\EventBusInterface     $bus
     */
    public function handleEvent($message, EventBusInterface $bus): void;
}
