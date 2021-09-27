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
use BetaKiller\Service\MaintenanceModeService;
use Interop\Queue\Consumer;
use Interop\Queue\Context;
use Interop\Queue\Message;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

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
     * @var \BetaKiller\Service\MaintenanceModeService
     */
    private MaintenanceModeService $maintenance;

    /**
     * NotificationWorkerDaemon constructor.
     *
     * @param \Interop\Queue\Context                                $context
     * @param \BetaKiller\MessageBus\BoundedEventTransportInterface $eventTransport
     * @param \BetaKiller\Notification\MessageSerializer            $serializer
     * @param \BetaKiller\Notification\NotificationFacade           $notification
     * @param \BetaKiller\Service\MaintenanceModeService            $maintenance
     * @param \Psr\Log\LoggerInterface                              $logger
     */
    public function __construct(
        Context                        $context,
        BoundedEventTransportInterface $eventTransport,
        MessageSerializer              $serializer,
        NotificationFacade             $notification,
        MaintenanceModeService         $maintenance,
        LoggerInterface                $logger
    ) {
        $this->context        = $context;
        $this->eventTransport = $eventTransport;
        $this->serializer     = $serializer;
        $this->notification   = $notification;
        $this->logger         = $logger;
        $this->maintenance    = $maintenance;
    }

    public function startDaemon(LoopInterface $loop): PromiseInterface
    {
        $regularQueue  = $this->context->createQueue(NotificationFacade::QUEUE_NAME_REGULAR);
        $priorityQueue = $this->context->createQueue(NotificationFacade::QUEUE_NAME_PRIORITY);

        $regularConsumer  = $this->context->createConsumer($regularQueue);
        $priorityConsumer = $this->context->createConsumer($priorityQueue);

        $loop->addPeriodicTimer(1, function () use ($regularConsumer, $priorityConsumer) {
            // Prevent subsequent calls upon processing
            if (!$this->isIdle()) {
                return;
            }

            // Prevent sending notifications during maintenance
            if ($this->maintenance->isEnabled()) {
                return;
            }

            $this->markAsProcessing();

            // Process priority messages first
            if (!$this->processConsumer($priorityConsumer)) {
                $this->processConsumer($regularConsumer);
            }

            $this->markAsIdle();
        });

        $this->subscribeForDismissibleEvents($loop);

        return resolve();
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
            LoggerHelper::logRawException($this->logger, $e);

            // Temp fix for failing tasks
            return false;
        }
    }

    private function subscribeForDismissibleEvents(LoopInterface $loop): void
    {
        // For each dismissible message
        foreach ($this->notification->getDismissibleMessages() as $messageCodename => $eventsNames) {
            $isBroadcast = $this->notification->isBroadcastMessage($messageCodename);

            // Iterate over events
            foreach ($eventsNames as $eventClassName) {
                $this->checkEvent($eventClassName, $isBroadcast);

                $this->logger->debug('Listening :event event to dismiss ":message" message', [
                    ':event'   => $eventClassName,
                    ':message' => $messageCodename,
                ]);

                /** @uses \BetaKiller\MessageBus\ExternalEventMessageInterface::getExternalName() */
                $eventName = forward_static_call([$eventClassName, 'getExternalName']);

                if (!$eventName) {
                    throw new NotificationException('Can not detect external name for event :fqn', [
                        ':fqn' => $eventClassName,
                    ]);
                }

                // Subscribe for provided event
                $this->eventTransport->subscribeBounded(
                    $eventName,
                    function (EventMessageInterface $event) use ($messageCodename, $isBroadcast) {
                        if ($isBroadcast) {
                            $this->onDismissBroadcastEvent($event, $messageCodename);
                        } else {
                            $this->onDismissDirectEvent($event, $messageCodename);
                        }

                        return resolve();
                    }
                );
            }
        }

        $this->eventTransport->startConsuming($loop);
    }

    private function checkEvent(string $eventFQN, bool $isBroadcast): void
    {
        if ($isBroadcast && !is_a($eventFQN, DismissBroadcastOnEventMessageInterface::class, true)) {
            throw new NotificationException('Event ":event" must implement :must to dismiss broadcast messages', [
                ':event' => $eventFQN,
                ':must'  => DismissBroadcastOnEventMessageInterface::class,
            ]);
        }

        if (!$isBroadcast && !is_a($eventFQN, DismissDirectOnEventMessageInterface::class, true)) {
            throw new NotificationException('Event ":event" must implement :must to dismiss direct messages', [
                ':event' => $eventFQN,
                ':must'  => DismissDirectOnEventMessageInterface::class,
            ]);
        }
    }

    private function onDismissBroadcastEvent(EventMessageInterface $event, string $messageCodename): void
    {
        $this->markAsProcessing();

        if (!$event instanceof DismissBroadcastOnEventMessageInterface) {
            throw new NotificationException('Event ":event" must implement :must to dismiss broadcast messages', [
                ':event' => \get_class($event),
                ':must'  => DismissBroadcastOnEventMessageInterface::class,
            ]);
        }

        $this->logger->debug('Dismiss broadcast event :event fired', [
            ':event'   => \get_class($event),
            ':message' => $messageCodename,
        ]);

        $this->notification->dismissBroadcast($messageCodename);

        $this->markAsIdle();
    }

    private function onDismissDirectEvent(EventMessageInterface $event, string $messageCodename): void
    {
        $this->markAsProcessing();

        if (!$event instanceof DismissDirectOnEventMessageInterface) {
            throw new NotificationException('Event ":event" must implement :must to dismiss direct messages', [
                ':event' => \get_class($event),
                ':must'  => DismissDirectOnEventMessageInterface::class,
            ]);
        }

        $this->logger->debug('Dismiss direct event :event fired', [
            ':event'   => \get_class($event),
            ':message' => $messageCodename,
        ]);

        foreach ($event->getDismissibleTargets() as $target) {
            $this->notification->dismissDirect($messageCodename, $target);
        }

        $this->markAsIdle();
    }

    public function stopDaemon(LoopInterface $loop): PromiseInterface
    {
        $this->context->close();

        $this->eventTransport->stopConsuming($loop);

        return resolve();
    }
}
