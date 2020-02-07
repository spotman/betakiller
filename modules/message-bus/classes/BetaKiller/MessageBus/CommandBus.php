<?php
namespace BetaKiller\MessageBus;

use BetaKiller\Daemon\CommandBusWorkerDaemon;
use Interop\Queue\Context;
use Interop\Queue\Producer;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class CommandBus extends AbstractMessageBus implements CommandBusInterface
{
    /**
     * @var \Interop\Queue\Context
     */
    private $queueContext;

    /**
     * @var \Interop\Queue\Producer|null
     */
    private $queueProducer;

    /**
     * @var \Interop\Queue\Queue
     */
    private $queue;

    /**
     * CommandBus constructor.
     *
     * @param \Interop\Queue\Context            $context
     * @param \Psr\Container\ContainerInterface $container
     * @param \Psr\Log\LoggerInterface          $logger
     */
    public function __construct(Context $context, ContainerInterface $container, LoggerInterface $logger)
    {
        parent::__construct($container, $logger);

        $this->queueContext = $context;
        $this->queue        = $this->queueContext->createQueue(CommandBusWorkerDaemon::QUEUE_NAME);
    }

    /**
     * @param \BetaKiller\MessageBus\CommandMessageInterface $command
     */
    public function enqueue(CommandMessageInterface $command): void
    {
        // Execute command through ESB bus
        $body = \serialize($command);

        $queueMessage = $this->queueContext->createMessage($body);

        // Enqueue
        $this->getProducer()->send($this->queue, $queueMessage);
    }

    protected function getMessageHandlersLimit(): int
    {
        // One command => one handler
        return 1;
    }

    /**
     * @param \BetaKiller\MessageBus\CommandMessageInterface $command
     *
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function handle(CommandMessageInterface $command): void
    {
        // Only one handler per message
        $handlerClassName = $this->getMessageHandlersClassNames($command)[0];

        /** @var callable $handler */
        $handler = $this->getHandlerInstance($handlerClassName);

        $handler($command);
    }

    private function getProducer(): Producer
    {
        if (!$this->queueProducer) {
            $this->queueProducer = $this->queueContext->createProducer()->setTimeToLive(600 * 1000);
        }

        return $this->queueProducer;
    }
}
