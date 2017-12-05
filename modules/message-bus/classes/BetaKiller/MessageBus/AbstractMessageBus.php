<?php
namespace BetaKiller\MessageBus;

use BetaKiller\Helper\LoggerHelperTrait;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

abstract class AbstractMessageBus
{
    use LoggerHelperTrait;
    use LoggerAwareTrait;

    /**
     * @var \BetaKiller\MessageBus\MessageInterface[]
     */
    private $processedMessages = [];

    /**
     * @var \BetaKiller\MessageBus\MessageHandlerInterface[][]
     */
    private $handlers = [];

    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    abstract protected function getHandlerInterface(): string;

    abstract protected function getMessageHandlersLimit(): int;

    abstract protected function _process($message, $handler): void;

    /**
     * @param string $messageClassName
     * @param        $handler
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
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

    /**
     * @param \BetaKiller\MessageBus\MessageInterface $message
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function emit(MessageInterface $message): void
    {
        $this->handle($message);

        // Add message
        $this->processedMessages[] = $message;
    }

    /**
     * @param \BetaKiller\MessageBus\MessageInterface $message
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
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

    private function process($message, $handler): void
    {
        // Wrap every message bus processing with try-catch block and log exceptions
        try {
            $this->_process($message, $handler);
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);
        }
    }

    private function getMessageName(MessageInterface $message): string
    {
        return get_class($message);
    }
}
