<?php
declare(strict_types=1);

namespace BetaKiller\MessageBus;

class EventBus extends AbstractMessageBus implements EventBusInterface
{
    /**
     * @param \BetaKiller\MessageBus\EventMessageInterface $message
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function emit(EventMessageInterface $message): void
    {
        $this->handle($message);

        // Add message
        $this->addProcessedMessage($message);
    }

    /**
     * @param \BetaKiller\MessageBus\EventMessageInterface $message
     * @param \BetaKiller\MessageBus\EventHandlerInterface $handler
     */
    protected function processMessage($message, $handler): void
    {
        $handler->handleEvent($message);
    }

    /**
     * @param \BetaKiller\MessageBus\EventMessageInterface $message
     * @param \BetaKiller\MessageBus\EventHandlerInterface $handler
     */
    protected function processDelayedMessage($message, $handler): void
    {
        $this->processMessage($message, $handler);
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

    /**
     * @param \BetaKiller\MessageBus\EventMessageInterface $message
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    private function handle(EventMessageInterface $message): void
    {
        foreach ($this->getHandlers($message) as $handler) {
            $this->process($message, $handler);
        }
    }

    /**
     * @param $message
     * @param $handler
     */
    private function process($message, $handler): void
    {
        // Wrap every message bus processing with try-catch block and log exceptions
        try {
            $handler = $this->reviewHandler($handler);
            $this->processMessage($message, $handler);
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);
        }
    }
}
