<?php
namespace BetaKiller\MessageBus;

interface EventHandlerInterface extends MessageHandlerInterface
{
    /**
     * @param \BetaKiller\MessageBus\EventMessageInterface $message
     * @param \BetaKiller\MessageBus\EventBus              $bus
     */
    public function handleEvent($message, EventBus $bus): void;
}
