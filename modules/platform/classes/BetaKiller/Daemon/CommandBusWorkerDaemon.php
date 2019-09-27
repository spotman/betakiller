<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\MessageBus\CommandBusInterface;
use BetaKiller\MessageBus\CommandMessageInterface;
use BetaKiller\Model\AbstractEntityInterface;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;

class CommandBusWorkerDaemon implements DaemonInterface
{
    use LoggerHelperTrait;

    public const CODENAME   = 'CommandBusWorker';
    public const QUEUE_NAME = 'commands';

    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private $config;

    /**
     * @var \BetaKiller\MessageBus\CommandBusInterface
     */
    private $commandBus;

    /**
     * @var \Interop\Queue\Context
     */
    private $queueContext;

    /**
     * @var \Interop\Queue\Queue
     */
    private $queue;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * CommandBusWorkerDaemon constructor.
     *
     * @param \BetaKiller\MessageBus\CommandBusInterface $commandBus
     * @param \BetaKiller\Config\ConfigProviderInterface $config
     * @param \Interop\Queue\Context                     $context
     * @param \Psr\Log\LoggerInterface                   $logger
     */
    public function __construct(
        CommandBusInterface $commandBus,
        ConfigProviderInterface $config,
        Context $context,
        LoggerInterface $logger
    ) {
        $this->commandBus   = $commandBus;
        $this->config       = $config;
        $this->queueContext = $context;
        $this->queue        = $this->queueContext->createQueue(self::QUEUE_NAME);
        $this->logger       = $logger;
    }

    public function start(LoopInterface $loop): void
    {
        // Load all commands from config
        foreach ((array)$this->config->load(['commands']) as $commandClass => $handlerClass) {
            $this->commandBus->on($commandClass, $handlerClass);

            $this->logger->debug('Bind :cmd to :handler', [
                ':cmd'     => $commandClass,
                ':handler' => $handlerClass,
            ]);
        }

        $consumer = $this->queueContext->createConsumer($this->queue);

        // Listen for ESB bus queue messages and call local handlers
        $loop->addPeriodicTimer(0.5, function () use ($consumer) {
            // Check message
            $message = $consumer->receiveNoWait();

            if ($message) {
                // process a message
                if ($this->processQueueMessage($message)) {
                    $consumer->acknowledge($message);

                    $this->logger->debug('ESB message ack for :msg', [
                        ':msg' => $message->getMessageId(),
                    ]);
                } else {
                    $consumer->reject($message);

                    $this->logger->debug('ESB message failed :msg', [
                        ':msg' => $message->getMessageId(),
                    ]);
                }
            }
        });
    }

    public function stop(): void
    {
        $this->queueContext->close();
    }

    private function processQueueMessage(Message $queueMessage): bool
    {
        try {
            // Unserialize message
            $message = \unserialize($queueMessage->getBody(), [
                CommandMessageInterface::class,
                AbstractEntityInterface::class,
            ]);

            $this->logger->debug('ESB command received :cmd', [
                ':cmd' => get_class($message),
            ]);

            // Local execute
            $this->commandBus->handle($message);

            return true;
        } catch (\Throwable $e) {
            $this->logException($this->logger, $e);

            // Temp fix for failing tasks
            return false;
        }
    }
}
