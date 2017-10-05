<?php
namespace BetaKiller\MessageBus;

class EventBus extends AbstractMessageBus
{
    /**
     * @param \BetaKiller\MessageBus\EventMessageInterface $message
     * @param \BetaKiller\MessageBus\EventHandlerInterface $handler
     */
    protected function process($message, $handler): void
    {
        $handler->handleEvent($message, $this);
    }

    protected function getHandlerInterface(): string
    {
        return EventHandlerInterface::class;
    }

    protected function getMessageHandlersLimit(): int
    {
        // No limit
        return 0;
    }
}
