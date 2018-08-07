<?php
namespace BetaKiller\MessageBus;

use BetaKiller\Helper\LoggerHelperTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractMessageBus implements AbstractMessageBusInterface
{
    use LoggerHelperTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \BetaKiller\MessageBus\MessageInterface[]
     */
    private $processedMessages = [];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \BetaKiller\MessageBus\MessageHandlerInterface[][]
     */
    private $handlers = [];

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger    = $logger;
    }

    /**
     * @return string
     */
    abstract protected function getHandlerInterface(): string;

    /**
     * @return int
     */
    abstract protected function getMessageHandlersLimit(): int;

    /**
     * @param \BetaKiller\MessageBus\MessageInterface        $message
     * @param \BetaKiller\MessageBus\MessageHandlerInterface $handler
     */
    abstract protected function processDelayedMessage($message, $handler): void;

    /**
     * @param string       $messageClassName
     * @param string|mixed $handler
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function on(string $messageClassName, $handler): void
    {
        $this->handlers[$messageClassName] = $this->handlers[$messageClassName] ?? [];

        $limit = $this->getMessageHandlersLimit();

        // Limit handlers count for CommandBus
        if ($limit && \count($this->handlers[$messageClassName]) > $limit) {
            throw new MessageBusException('Handlers limit exceed for :name message', [
                ':name' => $messageClassName,
            ]);
        }

        // Push handler
        $this->handlers[$messageClassName][] = $handler;

        // Handle all processed messages with new handler
        foreach ($this->processedMessages as $processedMessage) {
            if ($this->getMessageName($processedMessage) === $messageClassName) {
                $this->processDelayedMessage($processedMessage, $handler);
            }
        }
    }

    protected function getHandlers(MessageInterface $message): array
    {
        $name = $this->getMessageName($message);

        $handlers = $this->handlers[$name] ?? [];

        if (!$handlers && $message->handlersRequired()) {
            throw new MessageBusException('No handlers found for :name message', [':name' => $name]);
        }

        return $handlers;
    }

    protected function addProcessedMessage(MessageInterface $message): void
    {
        $this->processedMessages[] = $message;
    }

    /**
     * @param $handler
     *
     * @return mixed
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    protected function reviewHandler($handler)
    {
        // Convert class name to instance
        if (\is_string($handler)) {
            try {
                $handler = $this->container->get($handler);
            } catch (ContainerExceptionInterface $e) {
                throw MessageBusException::wrap($e);
            }
        }

        $handlerInterface = $this->getHandlerInterface();

        if (!($handler instanceof $handlerInterface)) {
            throw new MessageBusException('Handler :class must implement :must interface for using in :bus', [
                ':class' => \get_class($handler),
                ':must'  => $handlerInterface,
                ':bus'   => \get_class($this),
            ]);
        }

        return $handler;
    }

    protected function getMessageName(MessageInterface $message): string
    {
        return \get_class($message);
    }
}
