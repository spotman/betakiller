<?php
namespace BetaKiller\MessageBus;

use BetaKiller\Daemon\CommandBusWorkerDaemon;
use BetaKiller\Helper\LoggerHelper;
use Interop\Queue\Context;
use Interop\Queue\Producer;
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
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * CommandBus constructor.
     *
     * @param \Interop\Queue\Context   $context
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(Context $context, LoggerInterface $logger)
    {
        $this->queueContext = $context;
        $this->queue        = $this->queueContext->createQueue(CommandBusWorkerDaemon::QUEUE_NAME);
        $this->logger = $logger;
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
     * @return bool
     * @throws \BetaKiller\MessageBus\MessageBusException
     */
    public function handle(CommandMessageInterface $command): bool
    {
        // Only one handler per message
        $handler = $this->getMessageHandlers($command)[0];

        return $this->process($command, $handler);
    }

    /**
     * @param \BetaKiller\MessageBus\CommandMessageInterface $command
     * @param callable                                       $handler
     *
     * @return bool
     */
    private function process(CommandMessageInterface $command, callable $handler): bool
    {
        // Wrap every message bus processing with try-catch block and log exceptions
        try {
            $handler($command);

            return true;
        } catch (\Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);
        }

        return false;
    }

    private function getProducer(): Producer
    {
        if (!$this->queueProducer) {
            $this->queueProducer = $this->queueContext->createProducer()->setTimeToLive(600 * 1000);
        }

        return $this->queueProducer;
    }
}
