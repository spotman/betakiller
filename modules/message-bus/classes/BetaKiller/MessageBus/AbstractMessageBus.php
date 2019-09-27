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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \BetaKiller\MessageBus\MessageHandlerInterface[][]
     */
    private $bindings = [];

    /**
     * @var array MessageHandlerInterface[]
     */
    private $handlerInstances = [];

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
     * @param string $messageClassName
     * @param string $handlerClassName
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function on(string $messageClassName, string $handlerClassName): void
    {
        $this->bindings[$messageClassName] = $this->bindings[$messageClassName] ?? [];

        $limit = $this->getMessageHandlersLimit();

        // Limit handlers count for CommandBus
        if ($limit && \count($this->bindings[$messageClassName]) > $limit) {
            throw new MessageBusException('Handlers limit exceed for :name message', [
                ':name' => $messageClassName,
            ]);
        }

        // Push handler
        $this->bindings[$messageClassName][] = $handlerClassName;
    }

    /**
     * @param \BetaKiller\MessageBus\MessageInterface $message
     *
     * @return string[]
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    protected function getMessageHandlersClassNames(MessageInterface $message): array
    {
        $name = $this->getMessageName($message);

        $handlers = $this->bindings[$name] ?? [];

        if (!$handlers && $message instanceof EventMessageInterface && $message->handlersRequired()) {
            throw new MessageBusException('No handlers found for :name event', [':name' => $name]);
        }

        return $handlers;
    }

    /**
     * @param string $handlerName
     *
     * @return mixed
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    protected function getHandlerInstance(string $handlerName)
    {
        if (isset($this->handlerInstances[$handlerName])) {
            return $this->handlerInstances[$handlerName];
        }

        // Convert class name to instance
        try {
            $instance = $this->container->get($handlerName);
        } catch (ContainerExceptionInterface $e) {
            throw MessageBusException::wrap($e);
        }

        $handlerInterface = $this->getHandlerInterface();

        if (!($instance instanceof $handlerInterface)) {
            throw new MessageBusException('Handler :class must implement :must for using in :bus', [
                ':class' => \get_class($instance),
                ':must'  => $handlerInterface,
                ':bus'   => \get_class($this),
            ]);
        }

        $this->handlerInstances[$handlerName] = $instance;

        return $instance;
    }

    protected function getMessageName(MessageInterface $message): string
    {
        return \get_class($message);
    }
}
