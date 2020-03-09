<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Helper\LoggerHelper;
use BetaKiller\MessageBus\EventBusInterface;
use BetaKiller\MessageBus\EventMessageInterface;
use BetaKiller\Notification\DismissBroadcastOnEventMessageInterface;
use BetaKiller\Notification\DismissDirectOnEventMessageInterface;
use BetaKiller\Notification\MessageSerializer;
use BetaKiller\Notification\NotificationException;
use BetaKiller\Notification\NotificationFacade;
use Interop\Queue\Consumer;
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
     * @var \BetaKiller\MessageBus\EventBusInterface
     */
    private $eventBus;

    /**
     * NotificationWorkerDaemon constructor.
     *
     * @param \Interop\Queue\Context                      $context
     * @param \BetaKiller\Notification\MessageSerializer  $serializer
     * @param \BetaKiller\Notification\NotificationFacade $notification
     * @param \BetaKiller\MessageBus\EventBusInterface    $eventBus
     * @param \Psr\Log\LoggerInterface                    $logger
     */
    public function __construct(
        Context $context,
        MessageSerializer $serializer,
        NotificationFacade $notification,
        EventBusInterface $eventBus,
        LoggerInterface $logger
    ) {
        $this->context      = $context;
        $this->serializer   = $serializer;
        $this->notification = $notification;
        $this->eventBus     = $eventBus;
        $this->logger       = $logger;
    }

    public function start(LoopInterface $loop): void
    {
        $regularQueue  = $this->context->createQueue(NotificationFacade::QUEUE_NAME_REGULAR);
        $priorityQueue = $this->context->createQueue(NotificationFacade::QUEUE_NAME_PRIORITY);

        $regularConsumer  = $this->context->createConsumer($regularQueue);
        $priorityConsumer = $this->context->createConsumer($priorityQueue);

        // TODO Deal with listening of bounded ESB events via eventBus->on()
//        $this->listenForDismissibleEvents();

        $loop->addPeriodicTimer(0.5, function () use ($regularConsumer, $priorityConsumer) {
            // Process priority messages first
            if ($this->processConsumer($priorityConsumer)) {
                return;
            }

            $this->processConsumer($regularConsumer);
        });
    }

    private function processConsumer(Consumer $consumer): bool
    {
        // Check message
        $message = $consumer->receiveNoWait();

        if (!$message) {
            return false;
        }

        $result = $this->processMessage($message);

        if ($result) {
            $consumer->acknowledge($message);
        } else {
            $consumer->reject($message);
        }

        return $result;
    }

    private function processMessage(Message $queueMessage): bool
    {
        try {
            $this->logger->debug($queueMessage->getBody());

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

    private function listenForDismissibleEvents(): void
    {
        // For each dismissible message
        foreach ($this->notification->getDismissibleMessages() as $messageCodename => $eventsNames) {
            // Iterate over events
            foreach ($eventsNames as $eventName) {
                $this->logger->debug('Listening :event event to dismiss ":message" message', [
                    ':event'   => $eventName,
                    ':message' => $messageCodename,
                ]);

                // Listen to provided event
                $this->eventBus->on(
                    $eventName,
                    function (EventMessageInterface $event) use ($eventName, $messageCodename) {
                        $this->onDismissibleEvent($event, $eventName, $messageCodename);
                    }
                );
            }
        }
    }

    private function onDismissibleEvent(EventMessageInterface $event, string $eventName, string $messageCodename): void
    {
        $this->logger->debug('Dismissible event :event fired', [
            ':event'   => $eventName,
            ':message' => $messageCodename,
        ]);

        // Check message is broadcast
        if ($this->notification->isBroadcastMessage($messageCodename)) {
            // Check event type
            if (!$event instanceof DismissBroadcastOnEventMessageInterface) {
                throw new NotificationException('Event ":event" must implement :must to dismiss broadcast', [
                    ':event' => $eventName,
                    ':must'  => DismissBroadcastOnEventMessageInterface::class,
                ]);
            }

            $this->notification->dismissBroadcast($messageCodename);
        } else {
            // Check event type
            if (!$event instanceof DismissDirectOnEventMessageInterface) {
                throw new NotificationException('Event ":event" must implement :must to dismiss direct', [
                    ':event' => $eventName,
                    ':must'  => DismissDirectOnEventMessageInterface::class,
                ]);
            }

            $this->notification->dismissDirect($messageCodename, $event->getDismissibleTarget());
        }
    }

    public function stop(): void
    {
        $this->context->close();
    }
}
