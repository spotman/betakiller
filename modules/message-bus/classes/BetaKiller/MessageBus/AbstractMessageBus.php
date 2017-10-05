<?php
namespace BetaKiller\MessageBus;

abstract class AbstractMessageBus
{
    /**
     * @var \BetaKiller\MessageBus\MessageInterface[]
     */
    private $processedMessages = [];

    /**
     * @var \BetaKiller\MessageBus\MessageHandlerInterface[][]
     */
    private $handlers = [];

    abstract protected function getHandlerInterface(): string;

    abstract protected function getMessageHandlersLimit(): int;

    abstract protected function process($message, $handler): void;

    public function on(string $messageClassName, $handler): void
    {
        $handlerInterface = $this->getHandlerInterface();

        if (!($handler instanceof $handlerInterface)) {
            throw new MessageBusException('Handler :class must implement :must interface for using in :bus', [
                ':class' => get_class($handler),
                ':must' => $handlerInterface,
                ':bus' => get_class($this),
            ]);
        }

        $this->handlers[$messageClassName] = $this->handlers[$messageClassName] ?? [];

        $limit = $this->getMessageHandlersLimit();

        // Limit handlers count for CommandBus
        if ($limit && count($this->handlers[$messageClassName]) > $limit) {
            throw new MessageBusException('Handlers limit exceed for :name message', [
                ':name' => $messageClassName,
            ]);
        }

        // Push handler
        $this->handlers[$messageClassName][] = $handler;

        // Handle all processed messages with new handler
        foreach ($this->processedMessages as $processedMessage) {
            if ($this->getMessageName($processedMessage) === $messageClassName) {
                $this->process($processedMessage, $handler);
            }
        }
    }

    public function emit(MessageInterface $message): void
    {
        $this->handle($message);

        // Add message
        $this->processedMessages[] = $message;
    }
    
    private function handle(MessageInterface $message): void
    {
        $name = $this->getMessageName($message);
        
        $handlers = $this->handlers[$name] ?? [];

        if (!$handlers) {
            throw new MessageBusException('No handlers found for :name message', [':name' => $name]);
        }

        foreach ($handlers as $handler) {
            $this->process($message, $handler);
        }
    }
    
    private function getMessageName(MessageInterface $message): string
    {
        return get_class($message);
    }
}
