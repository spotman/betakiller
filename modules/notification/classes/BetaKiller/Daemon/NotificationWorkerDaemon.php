<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Notification\MessageSerializer;
use BetaKiller\Notification\NotificationFacade;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;

class NotificationWorkerDaemon implements DaemonInterface
{
    public const CODENAME = 'NotificationWorker';

    /**
     * @var \Interop\Queue\Context
     */
    private $context;

    /**
     * @var \BetaKiller\Notification\NotificationFacade
     */
    private $notification;

    /**
     * @var \BetaKiller\Notification\MessageSerializer
     */
    private $serializer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * NotificationWorkerDaemon constructor.
     *
     * @param \Interop\Queue\Context                      $context
     * @param \BetaKiller\Notification\MessageSerializer  $serializer
     * @param \BetaKiller\Notification\NotificationFacade $notification
     * @param \Psr\Log\LoggerInterface                    $logger
     */
    public function __construct(
        Context $context,
        MessageSerializer $serializer,
        NotificationFacade $notification,
        LoggerInterface $logger
    ) {
        $this->context      = $context;
        $this->serializer   = $serializer;
        $this->notification = $notification;
        $this->logger       = $logger;
    }

    public function start(LoopInterface $loop): void
    {
        $queue = $this->context->createQueue(NotificationFacade::QUEUE_NAME);

        $consumer = $this->context->createConsumer($queue);

        $loop->addPeriodicTimer(0.5, function () use ($consumer) {
            // Check message
            $message = $consumer->receiveNoWait();

            if ($message) {
                // process a message
                if ($this->processMessage($message)) {
                    $consumer->acknowledge($message);
                } else {
                    $consumer->reject($message);
                }
            }
        });
    }

    private function processMessage(Message $queueMessage): bool
    {
        try {
            // Unserialize message
            $message = $this->serializer->unserialize($queueMessage->getBody());

            // Send through transports
            return $this->notification->send($message);
        } catch (\Throwable $e) {
            LoggerHelper::logException($this->logger, $e);

            // Temp fix for failing tasks
            return false;
        }
    }

    public function stop(): void
    {
        $this->context->close();
    }
}
