<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Config\ConfigProviderInterface;
use BetaKiller\DI\ContainerInterface;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\MessageBus\CommandBusInterface;
use BetaKiller\MessageBus\CommandMessageInterface;
use BetaKiller\Model\AbstractEntityInterface;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Queue;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

final class CommandBusWorkerDaemon extends AbstractDaemon
{
    public const CODENAME   = 'CommandBusWorker';
    public const QUEUE_NAME = 'commands';

    /**
     * @var \BetaKiller\Config\ConfigProviderInterface
     */
    private ConfigProviderInterface $config;

    /**
     * @var \BetaKiller\MessageBus\CommandBusInterface
     */
    private CommandBusInterface $commandBus;

    /**
     * @var \Interop\Queue\Context
     */
    private Context $queueContext;

    /**
     * @var \Interop\Queue\Queue
     */
    private Queue $queue;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var \BetaKiller\DI\ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * CommandBusWorkerDaemon constructor.
     *
     * @param \BetaKiller\MessageBus\CommandBusInterface $commandBus
     * @param \BetaKiller\DI\ContainerInterface          $container
     * @param \BetaKiller\Config\ConfigProviderInterface $config
     * @param \Interop\Queue\Context                     $context
     * @param \Psr\Log\LoggerInterface                   $logger
     */
    public function __construct(
        CommandBusInterface $commandBus,
        ContainerInterface $container,
        ConfigProviderInterface $config,
        Context $context,
        LoggerInterface $logger
    ) {
        $this->commandBus   = $commandBus;
        $this->config       = $config;
        $this->queueContext = $context;
        $this->queue        = $this->queueContext->createQueue(self::QUEUE_NAME);
        $this->logger       = $logger;
        $this->container    = $container;
    }

    public function startDaemon(LoopInterface $loop): PromiseInterface
    {
        // Load all commands from config
        foreach ((array)$this->config->load('commands', []) as $commandClass => $handlerClass) {
            // Create handler instance
            $handler = $this->container->get($handlerClass);

            $this->commandBus->on($commandClass, $handler);

            $this->logger->debug('Bind :cmd to :handler', [
                ':cmd'     => $commandClass,
                ':handler' => $handlerClass,
            ]);
        }

        $consumer = $this->queueContext->createConsumer($this->queue);

        // Listen for ESB bus queue messages and call local handlers
        $loop->addPeriodicTimer(0.5, function () use ($consumer) {
            $this->markAsProcessing();

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
                    $consumer->reject($message, true);

                    $this->logger->debug('ESB message failed :msg', [
                        ':msg' => $message->getMessageId(),
                    ]);
                }
            }

            $this->markAsIdle();
        });

        return resolve();
    }

    public function stopDaemon(LoopInterface $loop): PromiseInterface
    {
        $this->queueContext->close();

        return resolve();
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
            return $this->commandBus->handle($message);
        } catch (\Throwable $e) {
            LoggerHelper::logRawException($this->logger, $e);

            // Temp fix for failing tasks
            return true;
        }
    }
}
