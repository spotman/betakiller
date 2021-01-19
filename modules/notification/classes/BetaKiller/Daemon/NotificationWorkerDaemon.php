<?php
declare(strict_types=1);

namespace BetaKiller\Daemon;

use BetaKiller\Helper\LoggerHelper;
use BetaKiller\MessageBus\BoundedEventTransportInterface;
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

final class NotificationWorkerDaemon extends AbstractDaemon
{
    public const CODENAME = 'NotificationWorker';

    /**
     * @var \Interop\Queue\Context
     */
    private Context $context;

    /**
     * @var \BetaKiller\Notification\NotificationFacade
     */
    private NotificationFacade $notification;

    /**
     * @var \BetaKiller\Notification\MessageSerializer
     */
    private MessageSerializer $serializer;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var \BetaKiller\MessageBus\BoundedEventTransportInterface
     */
    private BoundedEventTransportInterface $eventTransport;

    /**
     * NotificationWorkerDaemon constructor.
     *
     * @param \Interop\Queue\Context                                $context
     * @param \BetaKiller\MessageBus\BoundedEventTransportInterface $eventTransport
     * @param \BetaKiller\Notification\MessageSerializer            $serializer
     * @param \BetaKiller\Notification\NotificationFacade           $notification
     * @param \Psr\Log\LoggerInterface                              $logger
     */
    public function __construct(
        Context $context,
        BoundedEventTransportInterface $eventTransport,
        MessageSerializer $serializer,
        NotificationFacade $notification,
        LoggerInterface $logger
    ) {
        $this->context        = $context;
        $this->eventTransport = $eventTransport;
        $this->serializer     = $serializer;
        $this->notification   = $notification;
        $this->logger         = $logger;
    }

    public function startDaemon(LoopInterface $loop): void
    {
        $regularQueue  = $this->context->createQueue(NotificationFacade::QUEUE_NAME_REGULAR);
        $priorityQueue = $this->context->createQueue(NotificationFacade::QUEUE_NAME_PRIORITY);

        $regularConsumer  = $this->context->createConsumer($regularQueue);
        $priorityConsumer = $this->context->createConsumer($priorityQueue);

        $loop->addPeriodicTimer(0.5, function () use ($regularConsumer, $priorityConsumer) {
            $this->markAsProcessing();

            // Process priority messages first
            if (!$this->processConsumer($priorityConsumer)) {
                $this->processConsumer($regularConsumer);
            }

            $this->markAsIdle();
        });

        $this->subscribeForDismissibleEvents($loop);
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
            $consumer->reject($message, true);
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
            LoggerHelper::logRawException($this->logger, $e);

            // Temp fix for failing tasks
            return false;
        }
    }

    private function subscribeForDismissibleEvents(LoopInterface $loop): void
    {
        // For each dismissible message
        foreach ($this->notification->getDismissibleMessages() as $messageCodename => $eventsNames) {
            // Iterate over events
            foreach ($eventsNames as $eventName) {
                $this->logger->debug('Listening :event event to dismiss ":message" message', [
                    ':event'   => $eventName,
                    ':message' => $messageCodename,
                ]);

                // Subscribe for provided event
                $this->eventTransport->subscribeBounded(
                    $eventName,
                    function (EventMessageInterface $event) use ($messageCodename) {
                        $this->onDismissibleEvent($event, $messageCodename);
                    }
                );
            }
        }

        $this->eventTransport->startConsuming($loop);
    }

    private function onDismissibleEvent(EventMessageInterface $event, string $messageCodename): void
    {
        $this->markAsProcessing();

        $this->logger->debug('Dismissible event :event fired', [
            ':event'   => \get_class($event),
            ':message' => $messageCodename,
        ]);

        // Check message is broadcast
        if ($this->notification->isBroadcastMessage($messageCodename)) {
            // Check event type
            if (!$event instanceof DismissBroadcastOnEventMessageInterface) {
                throw new NotificationException('Event ":event" must implement :must to dismiss broadcast', [
                    ':event' => \get_class($event),
                    ':must'  => DismissBroadcastOnEventMessageInterface::class,
                ]);
            }

            $this->notification->dismissBroadcast($messageCodename);
        } else {
            // Check event type
            if (!$event instanceof DismissDirectOnEventMessageInterface) {
                throw new NotificationException('Event ":event" must implement :must to dismiss direct', [
                    ':event' => \get_class($event),
                    ':must'  => DismissDirectOnEventMessageInterface::class,
                ]);
            }

            $this->notification->dismissDirect($messageCodename, $event->getDismissibleTarget());
        }

        $this->markAsIdle();
    }

    public function stopDaemon(LoopInterface $loop): void
    {
        $this->context->close();

        $this->eventTransport->stopConsuming($loop);
    }
}
